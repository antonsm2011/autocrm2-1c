<?php
/** @var \Silex\Application $app */

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Silex\Provider\MonologServiceProvider;

$app->register(new MonologServiceProvider(), [
    "monolog.logfile" => __DIR__ . "/../logs/" . date("Y-m-d") . ".log",
    "monolog.level" => isset($app["log.level"]) ? $app["log.level"] : 'WARNING',
    "monolog.name" => "application"
]);

$app->extend('monolog', function (Logger $service) use ($app) {
    return $service->pushProcessor(function ($record) use ($app) {
        return array_merge($record, ['process_id' => $app['process_id']]);
    });
});

$app['monolog.handler'] = function () use ($app) {
    return new PDOHandler(
        $app['db'],
        MonologServiceProvider::translateLevel($app['monolog.level']),
        $app['monolog.bubble']
    );
};

class PDOHandler extends AbstractProcessingHandler
{
    private $initialized = false;
    /** @var PDO */
    private $pdo;
    /** @var PDOStatement */
    private $statement;

    public function __construct(PDO $pdo, $level = Logger::DEBUG, $bubble = true)
    {
        $this->pdo = $pdo;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->statement->execute(array(
            'time' => $record['datetime']->format('Y-m-d H:i:s'),
            'process_id' => $record['process_id'],
            'channel' => $record['channel'],
            'level' => Logger::getLevelName($record['level']),
            'message' => $record['message'],
            'context' => $this->serialize($record['context']),
            'extra' => $this->serialize($record['extra']),
        ));
    }

    private function initialize()
    {
        $this->pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS logs (
                time DATETIME,
                process_id CHAR(32),
                channel VARCHAR(255),
                level VARCHAR(15),
                message TEXT,
                context LONGTEXT,
                extra LONGTEXT
            )
SQL
        );
        $this->statement = $this->pdo->prepare(
            'INSERT INTO logs (time, process_id, channel, level, message, context, extra) '
                . 'VALUES (:time, :process_id, :channel, :level, :message, :context, :extra)'
        );

        $this->initialized = true;
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function serialize($data)
    {
        $formatter = new NormalizerFormatter();

        return json_encode($formatter->format($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
