<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\DSL\DSLQueryStack;
use ATPawelczyk\Elastic\DSL\DSLQueryStackInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class Request
 * @package ATPawelczyk\Elastic
 */
class Request
{
    /** @var HttpRequest */
    protected $request;

    /** @var DSLQueryStack|null */
    protected $dsl;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        if (null === $request = $requestStack->getCurrentRequest()) {
            throw new \RuntimeException('Request stack does not contain any request!');
        }

        $this->request = $request;
    }

    /**
     * @return DSLQueryStackInterface
     */
    public function getDSLQueryStack(): DSLQueryStackInterface
    {
        if ($this->dsl) {
            return $this->dsl;
        }

        $this->dsl = new DSLQueryStack();

        $content = $this->request->getContent();
        if (!is_string($content)) {
            throw new BadRequestHttpException(
                sprintf('Invalid request body. Expected string, got %s', gettype($content))
            );
        }

        foreach ($this->decodeContentToQueryObjects($content) as $queryObject) {
            $this->dsl->addQueryToStack($queryObject);
        }

        return $this->dsl;
    }

    private function decodeContentToQueryObjects(string $content): array
    {
        $queryObjects = [];
        foreach (explode(PHP_EOL, $content) as $queryString) {
            if (!$queryString) {
                continue;
            }
            $decodedQuery = json_decode($queryString, false);
            if (!$decodedQuery) {
                throw new BadRequestHttpException(
                    sprintf('Invalid request body. Expected valid JSON, got %s', $queryString)
                );
            }
            $queryObjects[] = $decodedQuery;
        }
        return $queryObjects;
    }
}
