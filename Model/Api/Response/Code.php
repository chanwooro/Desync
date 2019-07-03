<?php

namespace Dsync\Dsync\Model\Api\Response;

/**
 * Response code class
 */
class Code
{
    const HTTP_OK                 = 200;
    const HTTP_CREATED            = 201;
    const HTTP_ACCEPTED           = 202;
    const HTTP_MULTI_STATUS       = 207;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_INTERNAL_ERROR     = 500;


    /**
     * Get all of the response code status messages
     *
     * @return array
     */
    protected function getStatusMessages()
    {
        return array(
            self::HTTP_OK => 'OK',
            self::HTTP_CREATED => 'Created',
            self::HTTP_ACCEPTED => 'Accepted',
            self::HTTP_BAD_REQUEST => 'Bad Request',
            self::HTTP_UNAUTHORIZED => 'Unauthorized',
            self::HTTP_NOT_FOUND => 'Not Found',
            self::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::HTTP_INTERNAL_ERROR => 'Internal Server Error',
        );
    }

    /**
     * Return a default status message for a particular code
     *
     * @param int $code
     * @return string
     */
    public function getDefaultStatusMessage($code)
    {
        $statusMessages = $this->getStatusMessages();
        if (array_key_exists($code, $statusMessages)) {
            return $statusMessages[$code];
        }
        return null;
    }
}
