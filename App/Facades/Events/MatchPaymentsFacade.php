<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Events;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Services\Events\FindEventsWithMatchablePaymentsService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamByEventNumberService;
use InstruktoriBrno\TMOU\Services\Teams\SendPaymentsMatchingNotificationEmailService;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Tracy\Debugger;
use Tracy\ILogger;

class MatchPaymentsFacade
{
    public const RUNS = 'RUNS';

    private EntityManagerInterface $entityManager;

    private FindTeamByEventNumberService $findTeamByEventNumberService;

    private FindEventsWithMatchablePaymentsService $findEventsWithMatchablePaymentsService;

    private string $fioURL;

    private string $fioToken;

    private string $notificationEmail; // @phpstan-ignore-line

    private SendPaymentsMatchingNotificationEmailService $sendPaymentsMatchingNotificationEmailService;

    public function __construct(
        string $fioURL,
        string $fioToken,
        string $notificationEmail,
        EntityManagerInterface $entityManager,
        FindTeamByEventNumberService $findTeamByEventNumberService,
        FindEventsWithMatchablePaymentsService $findEventsWithMatchablePaymentsService,
        SendPaymentsMatchingNotificationEmailService $sendPaymentsMatchingNotificationEmailService
    ) {
        $this->fioURL = $fioURL;
        $this->fioToken = $fioToken;

        $this->entityManager = $entityManager;
        $this->findTeamByEventNumberService = $findTeamByEventNumberService;
        $this->findEventsWithMatchablePaymentsService = $findEventsWithMatchablePaymentsService;
        $this->notificationEmail = $notificationEmail;
        $this->sendPaymentsMatchingNotificationEmailService = $sendPaymentsMatchingNotificationEmailService;
    }

    private function getURL(DateTimeImmutable $from, DateTimeImmutable $to, bool $censorToken = false): string
    {
        return str_replace(
            ['__TOKEN__', '__FROM__', '__TO__'],
            [$censorToken ? 'CENSORED' : $this->fioToken, $from->format('Y-m-d'), $to->format('Y-m-d')],
            $this->fioURL
        );
    }

    /**
     * Takes care about matching payments of teams from future events
     *
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @throws \Exception
     */
    public function __invoke(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        $this->log(sprintf('STARTED AT %s', (new DateTimeImmutable)->format('Y-m-d H:i:s')));
        $url = $this->getURL($start, $end);
        $urlCensored = $this->getURL($start, $end, true);
        $this->log(sprintf('FETCHING DATA FROM %s TO %s from %s', $start->format('Y-m-d'), $end->format('Y-m-d'), $urlCensored));
        try {
            $data = Json::decode($this->getContentFromRemote($url));
            touch(__DIR__ . '/../../../payments/LAST_RUN');
            if (!isset($data->accountStatement->transactionList->transaction) || !is_array($data->accountStatement->transactionList->transaction)) {
                throw new \InstruktoriBrno\TMOU\Facades\Events\Exceptions\UnexpectedFormatException;
            }
            $transactions = $data->accountStatement->transactionList->transaction;
        } catch (\InstruktoriBrno\TMOU\Facades\Events\Exceptions\CannotDownloadRemoteEndpointException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            $this->log(sprintf('FETCHING DATA FAILED, EXITING.'));
            return;
        } catch (\Nette\Utils\JsonException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            $this->log(sprintf('PARSING DATA FAILED, EXITING.'));
            return;
        } catch (\InstruktoriBrno\TMOU\Facades\Events\Exceptions\UnexpectedFormatException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            $this->log(sprintf('UNEXPECTED DATA FORMAT, EXITING.'));
            return;
        }
        $this->log(sprintf('FETCHING DATA SUCCEEDED AND PARSED.'));


        // Find upcoming events and loop each of them
        $events = ($this->findEventsWithMatchablePaymentsService)();
        if (count($events) === 0) {
            $this->log(sprintf('NO EVENTS WITH PAYMENT DEADLINE YESTERDAY OR MORE IN THE FUTURE.'));
        }
        foreach ($events as $event) {
            if ($event->getPaymentPairingCodePrefix() === null || $event->getPaymentPairingCodePrefix() === '') {
                $this->log(sprintf('PROCESSING EVENT ID %s NUMBER %s SKIPPED AS PAYMENT PAIRING CONFIGURATION IS MISSING.', $event->getId(), $event->getNumber()));
                continue;
            }
            $sendNotification = false;
            $this->log(sprintf(
                'PROCESSING EVENT ID %s NUMBER %s WITH PREFIX %s AND SUFFIX LENGTH %s',
                $event->getId(),
                $event->getNumber(),
                $event->getPaymentPairingCodePrefix(),
                $event->getPaymentPairingCodeSuffixLength()
            ));
            foreach ($transactions as $transaction) {
                if (!isset($transaction->column1->value)
                    || !isset($transaction->column5)
                    || !isset($transaction->column5->value)
                    || !isset($transaction->column14)
                    || !isset($transaction->column14->value)
                    || $transaction->column14->value !== 'CZK') {
                    continue;
                }
                $vs = ltrim($transaction->column5->value, '0'); // some banks add leading zeros :-((
                $amount = $transaction->column1->value;
                if (Strings::startsWith($vs, $event->getPaymentPairingCodePrefix())) {
                    $number = Strings::substring($vs, Strings::length($event->getPaymentPairingCodePrefix()));
                    if (Strings::length($number) !== $event->getPaymentPairingCodeSuffixLength()) {
                        $this->log(sprintf(
                            'ATTENTION: Payment with VS %s (and amount %s) has matching payment prefix for event with number %s, however suffix lengths do not match!',
                            $vs,
                            $amount,
                            $event->getNumber()
                        ));
                        $sendNotification = true;
                    } else {
                        $team = ($this->findTeamByEventNumberService)($event, (int) $number);
                        if ($team === null) {
                            $this->log(sprintf(
                                'ATTENTION: Payment with VS %s (and amount %s) could not been matched as team with number %s could not been found within event with number %s!',
                                $vs,
                                $amount,
                                (int) $number,
                                $event->getNumber()
                            ));
                            $sendNotification = true;
                        } else {
                            if ($team->getGameStatus()->equals(GameStatus::REGISTERED())) {
                                $this->log(sprintf(
                                    'ATTENTION: Payment with VS %s (and amount %s) was paired to team with number %s (%s) in event with number %s. However the team is only REGISTERED, not QUALIFIED!',
                                    $vs,
                                    $amount,
                                    (int) $number,
                                    $team->getName(),
                                    $event->getNumber()
                                ));
                                $sendNotification = true;
                            } elseif ($team->getGameStatus()->equals(GameStatus::NOT_QUALIFIED())) {
                                $this->log(sprintf(
                                    'ATTENTION: Payment with VS %s (and amount %s) was paired to team with number %s (%s) in event with number %s. However this team is NOT_QUALIFIED!',
                                    $vs,
                                    $amount,
                                    (int) $number,
                                    $team->getName(),
                                    $event->getNumber()
                                ));
                                $sendNotification = true;
                            } elseif ($team->getGameStatus()->equals(GameStatus::PLAYING())) {
                                $this->log(sprintf(
                                    'ATTENTION: Payment with VS %s (and amount %s) was paired to team with number %s (%s) in event with number %s. However this team is already set as PLAYING!',
                                    $vs,
                                    $amount,
                                    (int) $number,
                                    $team->getName(),
                                    $event->getNumber()
                                ));
                                $sendNotification = true;
                            } elseif ($team->getGameStatus()->equals(GameStatus::QUALIFIED())) {
                                if ($team->getPaymentStatus()->equals(PaymentStatus::PAID())) {
                                    $this->log(sprintf(
                                        'ATTENTION: Payment with VS %s (and amount %s) was paired to team with number %s (%s) in event with number %s. However this team is already marked as PAID!',
                                        $vs,
                                        $amount,
                                        (int) $number,
                                        $team->getName(),
                                        $event->getNumber()
                                    ));
                                    $sendNotification = true;
                                } elseif ($event->getAmount() <= $amount) {
                                    $this->log(sprintf(
                                        'SUCCESS: Payment with VS %s (and amount %s) was successfully matched to team with number %s (%s) in event with number %s!',
                                        $vs,
                                        $amount,
                                        (int) $number,
                                        $team->getName(),
                                        $event->getNumber()
                                    ));
                                    $team->markAsPaid(new DateTimeImmutable());
                                    $team->changeTeamGameStatus(GameStatus::PLAYING());
                                    $this->entityManager->persist($team);
                                } else {
                                    $this->log(sprintf(
                                        'ATTENTION: Payment with VS %s (and amount %s) could not been matched to team with number %s (%s) in event %s due to amount being lower than requested!',
                                        $vs,
                                        $amount,
                                        (int) $number,
                                        $team->getName(),
                                        $event->getNumber()
                                    ));
                                    $sendNotification = true;
                                }
                            } else {
                                $this->log(sprintf(
                                    'ATTENTION: Payment with VS %s (and amount %s) was paired to team with number %s (%s) in event with number %s. However this team is in unknown state!',
                                    $vs,
                                    $amount,
                                    (int) $number,
                                    $team->getName(),
                                    $event->getNumber()
                                ));
                                $sendNotification = true;
                            }
                        }
                    }
                }
            }
            if ($sendNotification) {
                try {
                    ($this->sendPaymentsMatchingNotificationEmailService)($event);
                } catch (\Exception $exception) {
                    // prevent failing when notification sending fails, but still logs it
                    Debugger::log($exception, ILogger::EXCEPTION);
                }
                $this->log(sprintf('SENDING EVENT ID %s NUMBER %s NOTIFICATION FOR ATTENTION', $event->getId(), $event->getNumber()));
            }
            $this->log(sprintf('PROCESSING EVENT ID %s NUMBER %s FINISHED', $event->getId(), $event->getNumber()));
        }
        $this->entityManager->flush();
        // Foreach event loop over the downloaded log
        // Check whether the vs matches the prefix and number of digits... if so find team... get payment, write into the log, save.

        $this->log(sprintf('FINISHED AT %s', (new DateTimeImmutable)->format('Y-m-d H:i:s')));
    }

    private function log(string $message): void
    {
        file_put_contents(__DIR__ . '/../../../payments/' . self::RUNS, $message . "\n", FILE_APPEND);
    }

    private function getContentFromRemote(string $url): string
    {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            throw new \Exception('PHP CURL extension is not installed or loaded.');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        $output = curl_exec($ch);
        curl_close($ch);

        if (!is_string($output) || Strings::length($output) === 0) {
            throw new \InstruktoriBrno\TMOU\Facades\Events\Exceptions\CannotDownloadRemoteEndpointException;
        }

        return $output;
    }
}
