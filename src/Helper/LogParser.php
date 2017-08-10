<?php

namespace App\Helper;

use App\Model\Provider;
use \DateTime;
use \DateTimeZone;

class LogParser
{
    /**
     * A line of log parser
     *
     * @param string $log The text log
     * @param Provider $provider
     * @param string $tz A time zone identifier
     *
     * @return mixed An array where keys are internal tokens and values the corresponding values extracted
     *                            from the log file. Or false if line is not matchable.
     */
    public function parseLine($log, Provider $provider, $tz = null)
    {
        $regex = $provider->getRegex();
        $match = $provider->getMatch();
        $types = $provider->getFields();

        // If line is non matchable, return
        preg_match_all($regex, $log, $out, PREG_PATTERN_ORDER);
        if (@count($out[0]) === 0) {
            return false;
        }

        $result = array();
        $timestamp = 0;

        foreach ($match as $token => $key) {
            $str = null;
            $type = (isset ($types[$token])) ? $types[$token] : 'txt';

            if (substr($type, 0, 4) === 'date') {

                // Date is an array description with keys ( 'Y' : 5 , 'M' : 2 , ... )
                if (is_array($key) && ($this->isAssoc($key))) {
                    $newdate = array();
                    foreach ($key as $k => $v) {
                        $newdate[$k] = @$out[$v][0];
                    }

                    if (isset($newdate['U'])) {
                        $str = date('Y/m/d H:i:s', $newdate['U']);
                    } else {
                        if (isset($newdate['r'])) {
                            $str = date('Y/m/d H:i:s', $newdate['r']);
                        } else {
                            if (isset($newdate['c'])) {
                                $str = date('Y/m/d H:i:s', $newdate['c']);
                            } else {
                                if (isset($newdate['M'])) {
                                    $str = trim(
                                        $newdate['M'].' '.$newdate['d'].' '.$newdate['H'].':'.$newdate['i'].':'.$newdate['s'].' '.$newdate['Y'].' '.@$newdate['z']
                                    );
                                } elseif (isset($newdate['m'])) {
                                    $str = trim(
                                        $newdate['Y'].'/'.$newdate['m'].'/'.$newdate['d'].' '.$newdate['H'].':'.$newdate['i'].':'.$newdate['s'].' '.@$newdate['z']
                                    );
                                }
                            }
                        }
                    }
                } // Date is an array description without keys ( 2 , ':' , 3 , '-' , ... )
                else {
                    if (is_array($key)) {
                        $str = '';
                        foreach ($key as $v) {
                            $str .= (is_string($v)) ? $v : @$out[$v][0];
                        }
                    } else {
                        $str = @$out[$key][0];
                    }
                }

                // remove part next to the last /
                $dateformat = (substr($type, 0, 5) === 'date:') ? substr($type, 5) : 'Y/m/d H:i:s';

                if (($p = strrpos($dateformat, '/')) !== false) {
                    $dateformat = substr($dateformat, 0, $p);
                }

                if (($timestamp = strtotime($str)) === false) {
                    $formatted_date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
                    $timestamp = 0;
                } else {

                    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                        $date = new DateTime();
                        $date->setTimestamp($timestamp);
                    } else {
                        $date = new DateTime("@".$timestamp);
                    }

                    if (!is_null($tz)) {
                        $date->setTimezone(new DateTimeZone($tz));
                    }

                    $formatted_date = $date->format($dateformat);
                    $timestamp = (int)$date->format('U');
                }

                $result[$token] = $date;
            } // Array description without keys ( 2 , ':' , 3 , '-' , ... )
            else {
                if (is_array($key)) {
                    $r = '';
                    foreach ($key as $v) {
                        $r .= (is_string($v)) ? $v : @$out[$v][0];
                    }
                    $result[$token] = $r;
                } else {
                    $result[$token] = @$out[$key][0];
                }
            }
        }

        if ($timestamp > 0) {
            $result['pmld'] = $timestamp;
        }

        return $result;
    }

    /**
     * Tell whether this is a associative array (object in javascript) or not (array in javascript)
     *
     * @param   array $arr the array to test
     *
     * @return  boolean        true if $arr is an associative array
     */
    protected function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
