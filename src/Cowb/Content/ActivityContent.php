<?php

namespace Cowb\Content;

use Bolt\Content;

class ActivityContent extends Content
{
    public function getDateTimeDescription()
    {
        $start = new \DateTime($this->get('start'));
        $end = new \DateTime($this->get('end'));
        $startDate = $start->format('d-m-Y');
        $endDate = $end->format('d-m-Y');
        if ($startDate == $endDate)
        {
            $endDate = '';
        }
        $startTime = $endTime = '';
        if (!$this->get('wholeday'))
        {
            $startTime = $start->format('H:i');
            $endTime = $end->format('H:i');
        }
        if ($endDate == '' && $startTime == $endTime)
        {
            $endTime = '';
        }
        $startStr = trim($startDate . ' ' . $startTime);
        $endStr = trim($endDate . ' ' . $endTime);
        $result = $startStr;
        if ($endStr != '')
        {
            $result .= ' - ' . $endStr;
        }
        return $result;
    }
}