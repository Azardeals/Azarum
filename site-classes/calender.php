<?php

class Calendars
{

    /**
     * Constructor
     */
    public function __construct($url = "")
    {
        $this->naviHref = $url;
    }

    /*     * ******************* PROPERTY ******************* */

    private $dayLabels = array("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");
    private $currentYear = 0;
    private $currentMonth = 0;
    private $currentDay = 0;
    private $currentDate = null;
    private $daysInMonth = 0;
    private $naviHref = null;
    private $eventHandler = "calenderEvent(this)";
    private $dataAboutDate = [];
    private $startDate = 0;
    private $endDate = [];
    private $dateArray = [];
    private $isAjax = false;

    /*     * ******************* PUBLIC ********************* */

    /**
     * print out the calendar
     */
    public function show($month, $year)
    {
        if (empty($year)) {
            $year = date("Y", time());
        }
        if (empty($month)) {
            $month = date("m", time());
        }
        $this->currentYear = $year;
        $this->currentMonth = $month;
        $this->daysInMonth = $this->_daysInMonth($month, $year);
        $content = '<div id="calendar">' .
                '<div class="box">' .
                $this->_createNavi() .
                '</div>' .
                '<div class="box-content">' .
                '<ul class="label">' . $this->_createLabels() . '</ul>';
        $content .= '<div class="clear"></div>';
        $content .= '<ul class="dates">';
        $weeksInMonth = $this->_weeksInMonth($month, $year);
        // Create weeks in a month
        for ($i = 0; $i < $weeksInMonth; $i++) {
            //Create days in a week
            for ($j = 1; $j <= 7; $j++) {
                $content .= $this->_showDay($i * 7 + $j);
            }
        }
        $content .= '</ul>';
        $content .= '<div class="clear"></div>';
        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }

    /*     * ******************* PRIVATE ********************* */

    /**
     * create the li element for ul
     */
    private function _showDay($cellNumber)
    {
        if ($this->currentDay == 0) {
            $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
            if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
                $this->currentDay = 1;
            }
        }
        if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
            $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
            $cellContent = $this->currentDay;
            $this->currentDay++;
        } else {
            $this->currentDate = null;
            $cellContent = null;
        }
        $today = ($this->currentDate == date('Y-m-d')) ? "today" : "";
        $dateData = '';
        $dateData1 = '';
        $click = '';
        $class = "disabled";
        $title = "";
        if (in_array($this->currentDate, $this->dateArray) && ($this->currentDate >= date('Y-m-d'))) {
            $class = "available";
            $dateData = isset($this->dataAboutDate[$this->currentDate]['price']) ? $this->dataAboutDate[$this->currentDate]['price'] : '';
            $dateData1 = isset($this->dataAboutDate[$this->currentDate]['stock']) ? $this->dataAboutDate[$this->currentDate]['stock'] : '';
            $click = $this->eventHandler;
            $class = isset($this->dataAboutDate[$this->currentDate]['type']) ? $this->dataAboutDate[$this->currentDate]['type'] : 'available';
            if ($class == "block" || $class == "Unavailable") {
                $title = "Unavailable";
                $click = "";
            }
            if ($class == "backend_block") {
                $class = "block";
                $title = "Unavailable";
            }
        }
        return '<li  class=" ' . $class . ' ' . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
                ($cellContent == null ? 'mask' : '') . " " . $today . '"  title="' . $title . '"> <a id="' . $this->currentDate . '" onClick=' . $click . '>' . $cellContent . html_entity_decode($dateData) . html_entity_decode($dateData1) . '</a></li>';
    }

    /**
     * create navigation
     */
    private function _createNavi()
    {
        $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth) + 1;
        $nextYear = $this->currentMonth == 12 ? intval($this->currentYear) + 1 : $this->currentYear;
        $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth) - 1;
        $preYear = $this->currentMonth == 1 ? intval($this->currentYear) - 1 : $this->currentYear;
        $startDate = explode('-', $this->startDate);
        $startYear = $startDate[0];
        $startMonth = $startDate[1];
        $endDate = explode('-', $this->endDate);
        $endYear = $endDate[0];
        $endMonth = $endDate[1];
        $str = '<div class="header">';
        //	if($preYear>$startYear || ($preYear==$startYear && $preMonth<=$startMonth )){
        $click = "";
        $href = $this->naviHref . '&month=' . sprintf('%02d', $preMonth) . '&year=' . $preYear . '#calendar';
        if ($this->isAjax) {
            $click = 'onclick="fetchMonthValue(' . $preMonth . ',' . $preYear . ')"';
            $href = "javascript:void(0)";
        }
        $str .= '<a class="prev" ' . $click . ' href="' . $href . '">Prev</a>';
        //	}
        $str .= '<span class="yeartxt">' . date('Y M', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</span>';
        //	if($endYear>$nextyear || ($endYear==$nextyear && $endMonth>=$nextMonth)){
        $click = "";
        $href = $this->naviHref . '&month=' . sprintf("%02d", $nextMonth) . '&year=' . $nextYear . '#calendar';
        if ($this->isAjax) {
            $click = 'onclick="fetchMonthValue(' . $nextMonth . ',' . $nextYear . ')"';
            $href = "javascript:void(0)";
        }
        $str .= '<a class="next" ' . $click . ' href="' . $href . '">Next</a>';
        //	}	
        $str .= '</div>';
        return $str;
    }

    /**
     * create calendar week labels
     */
    private function _createLabels()
    {
        $content = '';
        foreach ($this->dayLabels as $index => $label) {
            $content .= '<li class="' . ($label == 6 ? 'end ' : 'start') . ' ">' . $label . '</a></li>';
        }
        return $content;
    }

    /**
     * calculate number of weeks in a particular month
     */
    private function _weeksInMonth($month = null, $year = null)
    {
        if (null == ($year)) {
            $year = date("Y", time());
        }
        if (null == ($month)) {
            $month = date("m", time());
        }
        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month, $year);
        $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
        $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
        $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));
        if ($monthEndingDay < $monthStartDay) {
            $numOfweeks++;
        }
        return $numOfweeks;
    }

    /**
     * calculate number of days in a particular month
     */
    private function _daysInMonth($month = null, $year = null)
    {
        if (null == ($year))
            $year = date("Y", time());
        if (null == ($month))
            $month = date("m", time());
        return date('t', strtotime($year . '-' . $month . '-01'));
    }

    /**
     *   Attach an event with date
     * */
    public function attachEventHandler($functionName)
    {
        $this->eventHandler = $functionName;
    }

    /**
     *   Set Data about date
     * */
    public function setDateData($data)
    {
        $this->dataAboutDate = $data;
    }

    function setAjax($flag)
    {
        $this->isAjax = $flag;
    }

    public function setDateRange($from, $to)
    {
        $this->startDate = $from;
        $this->endDate = $to;
        $start = $this->startDate;
        $end = $this->endDate;
        $dates = array($start);
        while (end($dates) < $end) {
            $dates[] = date('Y-m-d', strtotime(end($dates) . ' +1 day'));
        }
        $this->dateArray = $dates;
    }

}
