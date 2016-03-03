<?php

use Phinx\Migration\AbstractMigration;

class CreatePackagesTable extends AbstractMigration
{
    public function change()
    {
        $this->table('packages')
            ->addColumn('created_by', 'string', ['limit' => 50])
            ->addColumn('created_at', 'datetime')
            ->addColumn('processed_at', 'datetime', ['null' => true])
            ->addColumn('data', 'blob')
            ->create();
    }
}
