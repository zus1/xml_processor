<?php
declare(strict_types = 1);

namespace XmlProcessor\ValueObjects\DateWithMicrotime;

class DateWithMicrotime
{
    /**
     *
     * Generates suffix date_microtime for files
     *
     * @param string|null $date
     * @return mixed|string
     */
    public function get(?string $date="") {
        $microtime = str_replace(".", "", (string)microtime(true));
        try {
            if($date !== "") {
                $dt = new \DateTime($date);
            } else {
                $dt = new \DateTime();
            }
            $str = $dt->format("Y_m_d");
        } catch(\Exception $e) {
            return $microtime;
        }

        return sprintf("%s_%s", $str, $microtime);
    }
}