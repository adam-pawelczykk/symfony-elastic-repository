<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Result;

class BulkResult
{
    /** @var bool */
    private $success;
    /** @var DocumentResultCollection */
    private $details;

    public function __construct(
        bool                     $success,
        DocumentResultCollection $details = null
    ) {
        $this->success = $success;
        $this->details = $details ?? new DocumentResultCollection();
    }

    public function addDetail(DocumentResult $documentResult): void
    {
        $this->details->add($documentResult);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getDetails(): array
    {
        return $this->details->toArray();
    }

    public function getDetailsCollection(): DocumentResultCollection
    {
        return $this->details;
    }

    public static function mapFromResponse(array $response): self
    {
        $details = new DocumentResultCollection();

        foreach ($response['items'] as $item) {
            $itemResult = $item['index'] ?? [];

            if (empty($itemResult)) {
                continue;
            }

            $details->add(
                new DocumentResult(
                    $itemResult['_id'],
                    $itemResult['result']
                )
            );
        }

        return new self(
            $response['errors'] === false,
            $details
        );
    }
}
