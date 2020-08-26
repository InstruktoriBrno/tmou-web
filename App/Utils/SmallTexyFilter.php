<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Latte\Runtime\Html as LatteHtml;
use Nette\Utils\Html;
use Texy\HandlerInvocation;
use Texy\HtmlElement;
use Texy\Link;
use Texy\Modifier;
use Texy\Texy;
use function array_unique;
use function implode;

class SmallTexyFilter
{
    /** @var Texy */
    private $texy;

    public static function getSyntaxHelp(): Html
    {
        $el = Html::el('div');
        $el->addHtml(
            Html::el('pre')->setText(
                '
Můžete použít, **tučný** řez písma, *kurzívu* nebo ***obojí***.
"Text odkazu":https://www.tmou.cz/ nebo jen URL https://www.tmou.cz

- nečíslovaný
- seznam

1. číslovaný
2. seznam

> Toto jsou dlouhé citace odsazené jedním znakem >

Krátké citace můžete udělat pomocí >>takto<<. Referenci na jiný příspěvek vložíte pořadového čísla: [1].
Hodit se též může horní index^^takhle^^ nebo index^2, také spodní index__takhle__ nebo index_2.
'
            )
        );
        return $el;
    }

    public function getTexy(): Texy
    {
        if ($this->texy === null) {
            // Inspired by \Texy\Configurator::safeMode()
            $this->texy = new Texy();
            $this->texy->allowed = [
                'phrase/strong+em' => true,
                'phrase/strong' => true,
                'phrase/em' => true,
                'phrase/em-alt' => true,
                'phrase/em-alt2' => true,
                'phrase/sup' => true,
                'phrase/sup-alt' => true,
                'phrase/sub' => true,
                'phrase/sub-alt' => true,
                'phrase/quote' => true,
                'phrase/quicklink' => true,
                "phrase/span" => true,  // this allows "link":https://example.com
                'link/url' => true,
                'link/reference' => true,
                'link/email' => true,
                'blockquote' => true,
                'list' => true,
                'typography' => true,
                'longwords' => true,
            ];
            $this->texy->urlSchemeFilters[$this->texy::FILTER_ANCHOR] = '#https?:|ftp:|mailto:#A';
            $this->texy->linkModule->forceNoFollow = true;
            $this->texy->phraseModule->linksAllowed = true;
            $this->texy->allowedTags = [
                'strong' => Texy::NONE,
                'b' => Texy::NONE,
                'em' => Texy::NONE,
                'i' => Texy::NONE,
                'br' => Texy::NONE,
                'sup' => Texy::NONE,
                'sub' => Texy::NONE,
                'cite' => Texy::NONE,
                'a' => ['href', 'title'],
                'ul' => Texy::NONE,
                'ol' => Texy::NONE,
                'li' => Texy::NONE,
                'p' => Texy::NONE,
                'blockquote' => Texy::NONE,
            ];
            $this->texy->allowedStyles = Texy::NONE;
            $this->texy->allowedClasses = Texy::NONE;

            // references link [1] [2] will be processed to reference the Nth post
            $this->texy->addHandler('newReference', function (HandlerInvocation $parser, $refName): HtmlElement {
                $el = new HtmlElement('a');
                $el->attrs['href'] = '#post-' . $refName;
                $el->attrs['rel'] = 'nofollow';
                $el->setText("[$refName]");
                return $el;
            });

            // Add noopener and noreferrer to links
            $this->texy->addHandler('linkURL', function (HandlerInvocation $parser, $link) {
                if ($link instanceof Link) {
                    $el = $parser->getTexy()->linkModule->solve(null, $link, $link->URL);
                    if (!$el instanceof HtmlElement) {
                        return $parser->proceed();
                    }
                    $rels = [$el->attrs['rel'] ?? null];
                    $rels[] = 'noopener';
                    $rels[] = 'noreferrer';
                    $el->attrs['rel'] = implode(' ', array_unique($rels));
                    return $el;
                }
                return $parser->proceed();
            });
            $this->texy->addHandler('phrase', function (HandlerInvocation $parser, $phrase, $content, Modifier $modifier, Link $link = null) {
                if ($link instanceof Link) {
                    $el = $parser->getTexy()->linkModule->solve(null, $link, $content);
                    if (!$el instanceof HtmlElement) {
                        return $parser->proceed();
                    }
                    $rels = [$el->attrs['rel'] ?? null];
                    $rels[] = 'noopener';
                    $rels[] = 'noreferrer';
                    $el->attrs['rel'] = implode(' ', array_unique($rels));
                    return $el;
                }
                return $parser->proceed();
            });
        }

        return $this->texy;
    }

    public function __invoke(string $value): LatteHtml
    {
        return new LatteHtml($this->getTexy()->process($value));
    }
}
