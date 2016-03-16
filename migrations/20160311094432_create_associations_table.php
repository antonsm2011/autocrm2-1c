<?php

use Phinx\Migration\AbstractMigration;

class CreateAssociationsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('associations', ['id' => false, 'primary_key' => ['client', 'type', 'source_id']])
            ->addColumn('client', 'string')
            ->addColumn('type', 'string', ['limit' => 50])
            ->addColumn('source_id', 'string')
            ->addColumn('crm_id', 'integer', ['signed' => false])
            ->create();
    }
}
