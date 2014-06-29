<?php

namespace RavelryApi;

use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Stream\MetadataStreamInterface;
use GuzzleHttp\Stream\StreamInterface;

/**
 * These are some methods for normalizing parameters to/from API calls.
 * 
 * These need some work and aren't fully used yet.
 */
class TypeConversion
{
    public static function toDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        } elseif (is_string($value)) {
            return (new \DateTime($value))->format('Y-m-d');
        } elseif (is_int($value)) {
            return (new \DateTime())->setTimestamp($value)->format('Y-m-d');
        } else {
            return $value;
        }
    }

    public static function toDateTime($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('c');
        } elseif (is_string($value)) {
            return (new \DateTime($value))->format('c');
        } elseif (is_int($value)) {
            return (new \DateTime())->setTimestamp($value)->format('c');
        } else {
            return $value;
        }
    }

    public static function toInteger($value)
    {
        if (is_string($value)) {
            if ((string) (int) $value != $value) {
                throw new \UnexpectedValueException(sprintf('The value "%s" should be an integer.', $value));
            }

            return (int) $value;
        } else {
            return $value;
        }
    }

    public static function toFloat($value)
    {
        if (is_string($value)) {
            if ((string) (float) $value != $value) {
                throw new \UnexpectedValueException(sprintf('The value "%s" should be a float.', $value));
            }

            return (float) $value;
        } else {
            return $value;
        }
    }

    public static function toQuotedEtag($value)
    {
        if (preg_match('#^("|\')(.*)("|\')$#', $value)) {
            return $value;
        } else {
            return '"' . $value . '"';
        }
    }

    public static function toBoolean($value)
    {
        if (is_string($value)) {
            if (in_array($value, [ '0', 'false', 'no' ])) {
                return false;
            } elseif (in_array($value, [ '1', 'true', 'yes' ])) {
                return true;
            } else {
                throw new \UnexpectedValueException(sprintf('The value "%s" shoudl be a boolean.', $value));
            }
        } elseif (is_int($value)) {
            if (0 == $value) {
                return false;
            } elseif (1 == $value) {
                return true;
            } else {
                throw new \UnexpectedValueException(sprintf('The value "%s" shoudl be a boolean.', $value));
            }
        } else {
            return $value;
        }
    }

    /**
     * This is duplicating logic from `GuzzleHttp\Post\PostFile` in order to
     * patch odd API server behavior. It also makes sure the value is a proper
     * stream reference.
     *
     * See http://www.ravelry.com/discuss/ravelry-api/2936052/1-25#5
     */
    public static function toRavelryPostFile($value, Parameter $parameter)
    {
        if (is_string($value)) {
            $value = fopen($value, 'r');
        }

        if (!($value instanceof StreamInterface)) {
            $value = \GuzzleHttp\Stream\create($value);
        }

        if ($value instanceof MetadataStreamInterface) {
            $filename = $value->getMetadata('uri');
        }

        if (!$filename || substr($filename, 0, 6) === 'php://') {
            $filename = $parameter->getWireName();
        }

        return new PostFile(
            $parameter->getWireName(),
            $value,
            $filename,
            [
                'Content-Disposition' => sprintf(
                    'form-data; name="%s"; filename="%s"',
                    $parameter->getWireName(),
                    basename($filename)
                ),
            ]
        );
    }

    public static function toSpaceSeparated($value, Parameter $parameter)
    {
        if (is_array($value)) {
            return implode(' ', $value);
        }

        return $value;
    }
}
