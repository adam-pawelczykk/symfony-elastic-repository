<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Exception;

/**
 * Class DocumentParamConverterDenormalizeException
 * @package ATPawelczyk\Elastic\ParamConverter
 */
class DocumentParamConverterDenormalizeException extends \Exception
{
    private $data;

    /**
     * DocumentParamConverterDenormalizeException constructor.
     * @param string $class
     * @param mixed[] $data
     * @param \Throwable|null $previous
     */
    public function __construct(string $class, array $data, \Throwable $previous = null)
    {
        parent::__construct(sprintf("Unable to denormalize %s", $class), 0, $previous);

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
