<?php

namespace WhyooOs\Util;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;


class UtilSymfony
{

    /**
     * hack to get container where ever needed
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public static function getContainer()
    {
        // final solution: use global $kernel
        global $kernel;
        if (is_null($kernel)) {
            return null;
        }

        return $kernel->getContainer();
    }


    /**
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @see UtilFilesystem::sanitizeFilename()
     *
     * @param string $pathFile
     * @return Response
     */
    public static function createImageResponse(string $pathFile)
    {
        // Generate response
        $response = new Response();

        // headers .. cache for one day
        #$response->headers->set('Cache-Control', 'private');
        #$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-Type', mime_content_type($pathFile));
        $response->headers->set('Content-Length', filesize($pathFile));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400, public');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($pathFile));

        return $response;
    }

    /**
     * alternative / faster version of UtilSymfony::createImageResponse ..
     *
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @see UtilFilesystem::sanitizeFilename()
     *
     *
     * @param string $pathFile
     * @return BinaryFileResponse
     */
    public static function createFileResponse(string $pathFile)
    {
        BinaryFileResponse::trustXSendfileTypeHeader(); // "the X-Sendfile-Type header should be trusted" (?)

        return new BinaryFileResponse($pathFile);
    }


    /**
     * using JMS serializer
     *
     * @param mixed $data
     * @param null|array|string $groups
     * @return array
     */
    public static function toArray($data, $groups = null)
    {
        if( !is_array($data) && !is_object($data)) {
            return $data;
        }
        $serializationContext = UtilSymfony::getSerializationContext($groups);
        $serializer = UtilSymfony::getContainer()->get('jms_serializer');

        return $serializer->toArray($data, $serializationContext);
    }


    /**
     * @param null|array|string $groups Serialization Group Names
     * @return SerializationContext
     */
    public static function getSerializationContext($groups = null)
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();

        if (!is_null($groups)) {
            if (is_string($groups)) {
                $groups = [$groups];
            }
            $groups[] = "formatters"; // HACK
            $groups[] = "ALWAYS"; // HACK
            $context->setGroups($groups);
        }

        return $context;
    }


    /**
     * returns currently logged in user (if any) or null (if no user logged in)
     * @return User|null
     */
    public static function getUser()
    {
        if ($token = self::getContainer()->get('security.token_storage')->getToken()) {
            return $token->getUser();
        } else {
            return null;
        }
    }


}