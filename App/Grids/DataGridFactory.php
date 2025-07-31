<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids;

use Nette\ComponentModel\IContainer;
use Nette\SmartObject;
use Contributte\Datagrid\DataGrid;
use Contributte\Datagrid\Localization\SimpleTranslator;

class DataGridFactory
{
    use SmartObject;

    public function create(?IContainer $parent = null, ?string $name = null): DataGrid
    {
        $grid = new DataGrid($parent, $name);
        $grid->setTranslator(
            new SimpleTranslator([
                'contributte_datagrid.no_item_found_reset' => 'Nebyly nalezeny žádné položky, můžete zrušit filtry.',
                'contributte_datagrid.no_item_found' => 'Nebyly nalezeny žádné položky',
                'contributte_datagrid.here' => 'Zde',
                'contributte_datagrid.items' => 'Položky',
                'contributte_datagrid.all' => 'vše',
                'contributte_datagrid.from' => 'z',
                'contributte_datagrid.reset_filter' => 'Zrušit filtry',
                'contributte_datagrid.group_actions' => 'Skupinové akce',
                'contributte_datagrid.show' => 'Zobrazit',
                'contributte_datagrid.add' => 'Přidat',
                'contributte_datagrid.edit' => 'Upravit',
                'contributte_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
                'contributte_datagrid.show_default_columns' => 'Zobrazit výchozí sloupce',
                'contributte_datagrid.hide_column' => 'Skrýt sloupec',
                'contributte_datagrid.action' => 'Akce',
                'contributte_datagrid.previous' => 'Předchozí',
                'contributte_datagrid.next' => 'Následující',
                'contributte_datagrid.choose' => 'Vybrat',
                'contributte_datagrid.choose_input_required' => 'Text skupinové akce musí být vyplněn',
                'contributte_datagrid.execute' => 'Provést',
                'contributte_datagrid.save' => 'Uložit',
                'contributte_datagrid.cancel' => 'Zrušit',
                'contributte_datagrid.multiselect_choose' => 'Vybrat',
                'contributte_datagrid.multiselect_selected' => '{0} vybráno',
                'contributte_datagrid.filter_submit_button' => 'Filtr',
                'contributte_datagrid.show_filter' => 'Zobrazit filtr',
                'contributte_datagrid.per_page_submit' => 'Změnit',
            ])
        );
        $grid->setRememberState(false);
        $grid->setRefreshUrl(false);
        DataGrid::$iconPrefix = 'fa fa-';

        return $grid;
    }
}
