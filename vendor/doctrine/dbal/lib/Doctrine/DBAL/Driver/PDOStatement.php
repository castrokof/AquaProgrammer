<?php

namespace Doctrine\DBAL\Driver;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use PDO;

use function array_slice;
use function assert;
use function func_get_args;
use function is_array;
use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * The PDO implementation of the Statement interface.
 * Used by all PDO-based drivers.
 */
class PDOStatement extends \PDOStatement implements Statement
{
    private const PARAM_TYPE_MAP = [
        ParameterType::NULL         => PDO::PARAM_NULL,
        ParameterType::INTEGER      => PDO::PARAM_INT,
        ParameterType::STRING       => PDO::PARAM_STR,
        ParameterType::BINARY       => PDO::PARAM_LOB,
        ParameterType::LARGE_OBJECT => PDO::PARAM_LOB,
        ParameterType::BOOLEAN      => PDO::PARAM_BOOL,
    ];

    private const FETCH_MODE_MAP = [
        FetchMode::ASSOCIATIVE     => PDO::FETCH_ASSOC,
        FetchMode::NUMERIC         => PDO::FETCH_NUM,
        FetchMode::MIXED           => PDO::FETCH_BOTH,
        FetchMode::STANDARD_OBJECT => PDO::FETCH_OBJ,
        FetchMode::COLUMN          => PDO::FETCH_COLUMN,
        FetchMode::CUSTOM_OBJECT   => PDO::FETCH_CLASS,
    ];

    /**
     * Protected constructor.
     */
    protected function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode(int $mode, mixed ...$args): true
    {
        $mode = $this->convertFetchMode($mode);

        try {
            return parent::setFetchMode($mode, ...$args);
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        $type = $this->convertParamType($type);

        try {
            return parent::bindValue($param, $value, $type);
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * @param mixed    $param
     * @param mixed    $variable
     * @param int      $type
     * @param int|null $length
     * @param mixed    $driverOptions
     *
     * @return bool
     */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null, $driverOptions = null)
    {
        $type = $this->convertParamType($type);

        try {
            return parent::bindParam($param, $variable, $type, ...array_slice(func_get_args(), 3));
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeCursor()
    {
        try {
            return parent::closeCursor();
        } catch (\PDOException $exception) {
            // Exceptions not allowed by the interface.
            // In case driver implementations do not adhere to the interface, silence exceptions here.
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(?array $params = null): bool
    {
        try {
            return parent::execute($params);
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        try {
            return parent::fetch($this->convertFetchMode($mode), $cursorOrientation, $cursorOffset);
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $callArgs = [$this->convertFetchMode($mode), ...$args];

        try {
            $data = parent::fetchAll(...$callArgs);
            assert(is_array($data));

            return $data;
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn(int $columnIndex = 0): mixed
    {
        try {
            return parent::fetchColumn($columnIndex);
        } catch (\PDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * Converts DBAL parameter type to PDO parameter type
     *
     * @param int $type Parameter type
     */
    private function convertParamType(int $type): int
    {
        if (! isset(self::PARAM_TYPE_MAP[$type])) {
            // TODO: next major: throw an exception
            @trigger_error(sprintf(
                'Using a PDO parameter type (%d given) is deprecated and will cause an error in Doctrine DBAL 3.0',
                $type
            ), E_USER_DEPRECATED);

            return $type;
        }

        return self::PARAM_TYPE_MAP[$type];
    }

    /**
     * Converts DBAL fetch mode to PDO fetch mode
     *
     * @param int $fetchMode Fetch mode
     */
    private function convertFetchMode(int $fetchMode): int
    {
        if (! isset(self::FETCH_MODE_MAP[$fetchMode])) {
            // TODO: next major: throw an exception
            @trigger_error(sprintf(
                'Using a PDO fetch mode or their combination (%d given)' .
                ' is deprecated and will cause an error in Doctrine DBAL 3.0',
                $fetchMode
            ), E_USER_DEPRECATED);

            return $fetchMode;
        }

        return self::FETCH_MODE_MAP[$fetchMode];
    }
}
