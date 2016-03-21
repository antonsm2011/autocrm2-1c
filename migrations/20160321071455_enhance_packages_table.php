<?php

use Phinx\Migration\AbstractMigration;

class EnhancePackagesTable extends AbstractMigration
{
    public function up()
    {
        $this->table('packages')
            ->renameColumn('locked_by', 'processed_by')
            ->addColumn('status', 'enum', [
                'values' => ['new', 'checked', 'failed', 'saved'],
                'default' => 'new',
                'after' => 'processed_at',
            ])
            ->save();
        $statusSql = <<<'SQL'
            update packages 
            set 
                status = CASE
                    WHEN finished_at is not null THEN "saved"
                    WHEN processed_at is not null and processed_by is null and finished_at is null THEN "failed"
                    WHEN processed_by is not null THEN "checked"
                    ELSE "new"
                END,
                processed_at = COALESCE(finished_at, processed_at)  
SQL;
        $this->execute($statusSql);
        $this->table('packages')->removeColumn('finished_at')->save();
    }

    public function down()
    {
        $this->table('packages')
            ->addColumn('finished_at', 'datetime', ['after' => 'processed_at', 'null' => true])
            ->save();
        $revertSql = <<<'SQL'
            update packages 
            set 
                finished_at = CASE WHEN status = "saved" THEN processed_at ELSE null END,
                processed_by = CASE WHEN status = "checked" THEN processed_by ELSE null END
SQL;
        $this->execute($revertSql);
        $this->table('packages')
            ->removeColumn('status')
            ->renameColumn('processed_by', 'locked_by')
            ->save();
    }
}
