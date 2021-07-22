<?php

namespace AppTestBundle\Document\FunctionalTests;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 * @ODM\Indexes({
 *     @ODM\Index(keys={"clientIp" = "desc"}),
 *     @ODM\Index(keys={"localDatetime" = "desc"}),
 * })
 */
class RequestLog
{
    /**
     * @ODM\Id(strategy="AUTO")
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     */
    private $clientIp;

    /**
     * @ODM\Field(type="string")
     */
    private $localDatetime;

    private function __construct()
    {
    }

    public static function create(
        string $clientIp,
        \DateTimeInterface $localDatetime = null
    ) {
        $requestLog = new static();

        $requestLog->clientIp = $clientIp;
        $requestLog->localDatetime = $localDatetime ? $localDatetime->format('c') : null;

        return $requestLog;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function getLocalDatetime()
    {
        return $this->localDatetime;
    }
}
