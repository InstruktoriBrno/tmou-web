<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids;

use Nette\ComponentModel\IContainer;
use Nette\SmartObject;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

class DataGridFactory
{
    use SmartObject;

    public function create(?IContainer $parent = null, ?string $name = null): DataGrid
    {
        $grid = new DataGrid($parent, $name);
        $grid->setTranslator(
            new SimpleTranslator([
                'ublaboo_datagrid.no_item_found_reset' => 'Nebyly nalezeny žádné položky, můžete zrušit filtry.',
                'ublaboo_datagrid.no_item_found' => 'Nebyly nalezeny žádné položky',
                'ublaboo_datagrid.here' => 'Zde',
                'ublaboo_datagrid.items' => 'Položky',
                'ublaboo_datagrid.all' => 'vše',
                'ublaboo_datagrid.from' => 'z',
                'ublaboo_datagrid.reset_filter' => 'Zrušit filtry',
                'ublaboo_datagrid.group_actions' => 'Skupinové akce',
                'ublaboo_datagrid.show' => 'Zobrazit',
                'ublaboo_datagrid.add' => 'Přidat',
                'ublaboo_datagrid.edit' => 'Upravit',
                'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
                'ublaboo_datagrid.show_default_columns' => 'Zobrazit výchozí sloupce',
                'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
                'ublaboo_datagrid.action' => 'Akce',
                'ublaboo_datagrid.previous' => 'Předchozí',
                'ublaboo_datagrid.next' => 'Následující',
                'ublaboo_datagrid.choose' => 'Vybrat',
                'ublaboo_datagrid.choose_input_required' => 'Text skupinové akce musí být vyplněn',
                'ublaboo_datagrid.execute' => 'Provést',
                'ublaboo_datagrid.save' => 'Uložit',
                'ublaboo_datagrid.cancel' => 'Zrušit',
                'ublaboo_datagrid.multiselect_choose' => 'Vybrat',
                'ublaboo_datagrid.multiselect_selected' => '{0} vybráno',
                'ublaboo_datagrid.filter_submit_button' => 'Filtr',
                'ublaboo_datagrid.show_filter' => 'Zobrazit filtr',
                'ublaboo_datagrid.per_page_submit' => 'Změnit',
            ])
        );
        $grid->setRememberState(false);
        $grid->setRefreshUrl(false);
        DataGrid::$iconPrefix = 'fa fa-';

        return $grid;
    }
}
