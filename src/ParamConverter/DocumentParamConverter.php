<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\ParamConverter;

use ATPawelczyk\Elastic\Exception\DocumentParamConverterDenormalizeException;
use ATPawelczyk\Elastic\IndexManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Class DocumentParamConverter
 */
class DocumentParamConverter implements ParamConverterInterface
{
    private $denormalizer;
    private $indexManager;

    /**
     * ContentParamConverter2 constructor.
     * @param DenormalizerInterface $denormalizer
     * @param IndexManagerInterface $indexManager
     */
    public function __construct(DenormalizerInterface $denormalizer, IndexManagerInterface $indexManager)
    {
        $this->denormalizer = $denormalizer;
        $this->indexManager = $indexManager;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $class = $configuration->getClass();

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        if (false === $document = $this->find($class, $request, $name)) {
            if (! $configuration->isOptional()) {
                throw new \LogicException(sprintf('Unable to guess how to get a Elastic Document from the request information for parameter "%s".', $name));
            }

            $document = null;
        }

        if (null === $document  && false === $configuration->isOptional()) {
            throw new NotFoundHttpException('Invalid uuid given');
        }

        $request->attributes->set($name, $document);

        return true;
    }

    /**
     * @param string $class
     * @param Request $request
     * @param string $name
     * @return array|false|object|null
     * @throws DocumentParamConverterDenormalizeException
     */
    private function find(string $class, Request $request, string $name)
    {
        $id = $this->getIdentifier($request, $name);

        if (null === $id) {
            return false;
        }

        $index = $this->indexManager->getIndex($class);

        if (null !== $data = $index->source($id)) {
            try {
                return $this->denormalizer->denormalize($data, $class);
            } catch (ExceptionInterface $exception) {
                throw new DocumentParamConverterDenormalizeException($class, $data, $exception);
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param string $name
     * @return string|null
     */
    private function getIdentifier(Request $request, string $name): ?string
    {
        if ($request->attributes->has($name)) {
            return (string) $request->attributes->get($name);
        }

        return null;
    }

    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        if (empty($configuration->getClass())) {
            return false;
        }

        return $this->indexManager->hasIndex($configuration->getClass());
    }
}
