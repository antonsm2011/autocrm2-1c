<?php

use Phinx\Migration\AbstractMigration;

class AddFinishedAtColumnToPackages extends AbstractMigration
{
    public function change()
    {
        $this->table('packages')
            ->addColumn('locked_by', 'char', ['after' => 'created_at', 'limit' => 32, 'null' => true])
            ->addColumn('finished_at', 'datetime', ['after' => 'processed_at', 'null' => true])
            ->update();
    }
}
