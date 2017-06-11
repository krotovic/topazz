<?php
/**
 * @author  Lukáš
 * @version 1.0.0
 * @package Topazz
 */

namespace Topazz\Entity;


use Topazz\Data\Collection\ArrayList;
use Topazz\Database\Connector;
use Topazz\Database\Database;
use Topazz\Database\Proxy\Proxy;
use Topazz\Database\Table\Column;
use Topazz\Database\Table\Table;
use Topazz\Theme\Layout;

class Page extends ContentEntity {

    public $id;
    public $name;
    public $title;
    public $uri = "/";
    public $layout;
    public $project_id;
    public $project;

    public function __construct() {
        $this->project = Project::find("id", $this->project_id);
    }

    public static function getTableDefinition(): Table {
        return Table::create("pages")->columns(
            Column::id()
        );
    }

    public function authors(): Proxy {
        return new Proxy(
            Database::select()->from('users')->whereIn('id',
                Database::select('user_id')->distinct()
                    ->from('users_has_pages')
                    ->where('page_id', '=', $this->id)
            ), User::class
        );
    }

    public function posts(): Proxy {
        return new Proxy(
            Database::select()->from('posts')
                ->where('page_id', '=', $this->id),
            Post::class
        );
    }

    public function create() {
        // TODO: Implement create() method.
    }

    public function update() {
        // TODO: Implement update() method.
    }

    public function remove() {
        // TODO: Implement remove() method.
    }
}