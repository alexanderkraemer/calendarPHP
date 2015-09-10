<?php
namespace App\Http\Controllers\Kalender;

use Illuminate\Http\Request;
use Route;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KalenderController extends Controller
{
    private $month;
    private $year;
    private $months;

    public function index ( $month = '' )
    {
        if ( $month !== '' )
        {
            $this->month = $month;
        }

        $this->months = [
            1  => "January",
            2  => "February",
            3  => "March",
            4  => "April",
            5  => "May",
            6  => "June",
            7  => "July",
            8  => "August",
            9  => "September",
            10 => "October",
            11 => "November",
            12 => "December"
        ];
        $cal = $this->render ();

        return $cal;
    }

    public function render ()
    {
        $type        = 'monat';
        $this->month = date ( 'n' );
        $this->year  = date ( 'Y' );
        $url = explode ( '/', $_SERVER['PHP_SELF'] );
        unset( $url[0] );
        unset( $url[1] );
        unset( $url[2] );
        $url = implode ( '/', $url );
        $url = explode ( '/', $url );

        // Jahreszahl wurde übergeben
        if (is_numeric ( $url[0] ))
        {
            $this->year = $url[0];
            // Wenn als Parameter z.B. "monat/november" übergeben wird, wird hier im Monatsnamen Array der Index des
            // Monats gesucht.
            if(isset($url[2]))
            {
                if (in_array ( strtolower ( $url[2] ), array_map ( 'mb_strtolower', $this->months ) ) )
                {
                    // Der Index des Monats (zahl des Monats im Jahr) wird als Monat ausgewählt
                    $this->month = array_search ( strtolower ( $url[2] ), array_map ( 'mb_strtolower', $this->months ) );
                }
                elseif ( is_numeric($url[2]) )
                {
                    // Es kann auch eine Zahl als Monat angegeben werden.
                    // Solange diese sich zwischen 1 und 12 befindet.
                    // Andernfalls wird der aktuelle Monat gewählt.
                    $this->month = date('n');
                    if($url[2] > 0 AND $url[2] <= 12)
                    {
                        $this->month = $url[2];
                    }
                }
            }
        }
        // Parameter Monat wurde übergeben ohne Jahr
        // Für das Jahr wird das derzeitige Jahr gewählt
        elseif(isset($url[1]))
        {
            // Wenn als Parameter z.B. "monat/november" übergeben wird, wird hier im Monatsnamen Array der Index des
            // Monats gesucht.
            if ( in_array ( strtolower ( $url[1] ), array_map ( 'mb_strtolower', $this->months ) ) )
            {
                // Der Index des Monats (zahl des Monats im Jahr) wird als Monat ausgewählt
                $this->month = array_search ( strtolower ( $url[1] ), array_map ( 'mb_strtolower', $this->months ) );
            }
            elseif ( is_numeric($url[1]) )
            {
                // Es kann auch eine Zahl als Monat angegeben werden.
                // Solange diese sich zwischen 1 und 12 befindet.
                // Andernfalls wird der aktuelle Monat gewählt.
                $this->month = date('n');
                if($url[1] > 0 AND $url[1] <= 12)
                {
                    $this->month = $url[1];
                }
            }
        }



        if ( $type === 'monat' )
        {
            $html = $this->renderMonat ();
        }

        return $html;
    }

    /**
     * @return string
     */
    private function renderMonat ()
    {
        $cYear  = $this->year;
        $cMonth = $this->month;
        if ( $this->month == '' )
        {
            $cMonth = date ( 'n' );
        }
        if ( $this->year == '' )
        {
            $cYear = date ( 'Y' );
        }
        $monthNames = $this->months;
        $prev_year  = $cYear - 1;
        $next_year  = $cYear + 1;
        $prev_month = $cMonth - 1;
        $next_month = $cMonth + 1;
        $timestamp  = mktime ( 0, 0, 0, $cMonth, 1, $cYear );
        $maxday     = date ( "t", $timestamp );
        $thismonth  = getdate ( $timestamp );
        $startday   = $thismonth['wday'];
        if ( $prev_month == 0 )
        {
            $prev_month = 12;
            $prev_year  = $cYear - 1;
        }
        if ( $next_month == 13 )
        {
            $next_month = 1;
            $next_year  = $cYear + 1;
        }
        $html = '<div class="row">';
        $html .= '<h1 style="margin-left:50px;">';
        $html .= '<a style="text-decoration:none; margin-right:20px;" href="' . url() . '/kalender/' . $cYear .
                 '/monat/' . $prev_month .'">';
        $html .= '<';
        $html .= '</a>';
        $html .= $monthNames[ $cMonth ];
        $html .= '<a style="text-decoration:none; margin-left:20px;" href="' . url() . '/kalender/' . $cYear .
                 '/monat/' . $next_month .'">';
        $html .= '>';
        $html .= '</a>';
        $html .= '<a style="text-decoration:none; margin-left:40px; margin-right:20px;" href="' . url() .
                 '/kalender/' . $prev_year . '/monat/' . $cMonth .'">';
        $html .= '<';
        $html .= '</a>';
        $html .= $cYear;
        $html .= '<a style="text-decoration:none; margin-left:20px;" href="' . url() . '/kalender/' . $next_year .
                 '/monat/' . $cMonth .'">';
        $html .= '>';
        $html .= '</a>';
        $html .= '<a style="text-decoration:none; margin-left:80px;" href="' . url() .
                 '/kalender">';
        $html .= 'neuer Eintrag';
        $html .= '</a>';
        $html .= '<a style="text-decoration:none; margin-right:80px;" class="pull-right" href="' . url() .
                 '/kalender">';
        $html .= 'Heute';
        $html .= '</a>';
        $html .= '</h1>';
        $html .= '</div>';
        $html .= '<table class="" style="width:100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<td style="text-align:center;">KW</td>';
        $html .= '<td>Montag</td>';
        $html .= '<td>Dienstag</td>';
        $html .= '<td>Mittwoch</td>';
        $html .= '<td>Donnerstag</td>';
        $html .= '<td>Freitag</td>';
        $html .= '<td>Samstag</td>';
        $html .= '<td>Sonntag</td>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $anz = $maxday + $startday;
        $anz = ceil( $anz / 7);
        $anz = $anz * 7;
        for (
            $i = 1; $i <= $anz; $i++ )
        {
            $heute      = ( $i - $startday + 1 );
            $datumheute = strtotime ( ( $i - $startday + 1 ) . '-' . $cMonth . '-' . $cYear );
            if ( ( $i % 7 ) == 1 )
            {
                $html .= '<tr>';
                $html .= '<td class="kalenderwoche">' . date ( 'W', mktime ( 0, 0, 0, $cMonth, $i, $cYear ) ) . '</td>';
            }
            if ( $i < $startday OR $i >= ($maxday + $startday))
            {
                $html .= "<td class='border kalendertag outofmonth'></td>";
            }
            else
            {
                $html .= '<td class="border kalendertag';
                // DAs heutige Datum Markieren
                if ( $datumheute == strtotime ( date ( 'd-m-Y' ) ) )
                {
                    $html .= ' heute ';
                }
                $html .= '">';

                if ( $heute <= $maxday )
                {
                    $html .= '<p class="datefont">' . ( $i - $startday + 1 ) . '</p>';
                }
                $termine = DB::table ( 'termin' )
                    ->where('ter_date_from', '<=', ( $i - $startday + 1 ) . '-' . $cMonth . '-' . $cYear)
                    ->orWhere('ter_date_to', '>=', ( $i - $startday + 1 ) . '-' . $cMonth . '-' . $cYear)
                    ->get();

                foreach ( $termine as $termin )
                {
                    $tag_von = date ( "d-m-Y", strtotime ( $termin->ter_date_from ) );
                    $tag_bis = date ( "d-m-Y", strtotime ( $termin->ter_date_to ) );
                    if ( $tag_bis !== $tag_von )
                    {
                        // Wenn das Heutige Datum der Anfag des Events ist
                        if ( $datumheute == strtotime ( $tag_von ) )
                        {
                            $state            = 'anfang';
                            $trennzeichen_von = ':';
                            $trennzeichen_bis = '';
                            $stunde_von       = date ( "H", strtotime ( $termin->ter_date_from ) );
                            $minute_von       = date ( "i", strtotime ( $termin->ter_date_from ) );
                            $stunde_bis       = '';
                            $minute_bis       = '';
                        }
                        // Wenn das Heutige Datum weder Anfang noch ende des Events ist, dann ist es ein "Mittelstück"
                        elseif ( $datumheute > strtotime ( $tag_von ) AND $datumheute < strtotime ( $tag_bis ) )
                        {
                            $state            = 'mittel';
                            $trennzeichen_von = '';
                            $trennzeichen_bis = '';
                            $stunde_von       = '';
                            $minute_von       = '';
                            $stunde_bis       = '';
                            $minute_bis       = '';
                        }
                        // Wenn das Heutige Datum das Ende des Events ist
                        else
                        {
                            $state            = 'ende';
                            $trennzeichen_von = '';
                            $trennzeichen_bis = ':';
                            $stunde_von       = '';
                            $minute_von       = '';
                            $stunde_bis       = date ( "H", strtotime ( $termin->ter_date_to ) );
                            $minute_bis       = date ( "i", strtotime ( $termin->ter_date_to ) );
                        }
                    }
                    else
                    {
                        $state            = 'einzel';
                        $trennzeichen_von = ':';
                        $trennzeichen_bis = ':';
                        $stunde_von       = date ( "H", strtotime ( $termin->ter_date_from ) );
                        $minute_von       = date ( "i", strtotime ( $termin->ter_date_from ) );
                        $stunde_bis       = date ( "H", strtotime ( $termin->ter_date_to ) );
                        $minute_bis       = date ( "i", strtotime ( $termin->ter_date_to ) );
                    }
                    if ( strtotime ( $tag_bis ) === strtotime ( $tag_von ) AND $datumheute == strtotime ( $tag_bis ) )
                    {
                        $html .= '<div class="bs-callout bs-callout-default einfach">';
                        $html .= '<h4>' . $termin->ter_title . '</h4>';
                        $html .= '</div>';
                    }
                    elseif ( strtotime ( $tag_bis ) !== strtotime ( $tag_von ) AND
                             strtotime ( $tag_von ) == $datumheute
                    )
                    {
                        $html .= '<div class="bs-callout bs-callout-default start">';
                        $html .= '<h4>' . $termin->ter_title . '</h4>';
                        $html .= '</div>';
                    }
                    elseif ( strtotime ( $tag_bis ) !== strtotime ( $tag_von ) AND
                             strtotime ( $tag_bis ) == $datumheute
                    )
                    {
                        if ( ( $i % 7 ) == 1 )
                        {
                            $html .= '<div class="bs-callout bs-callout-default ripstart ende">';
                            $html .= '<h4>' . $termin->ter_title . '</h4>';
                            $html .= '</div>';
                        }
                        else
                        {
                            $html .= '<div class="bs-callout bs-callout-default ende">';
                            $html .= '<h4></h4>';
                            $html .= '</div>';
                        }
                    }
                    elseif ( ( $datumheute > strtotime ( $tag_von ) ) AND ( $datumheute < strtotime ( $tag_bis ) ) )
                    {
                        if ( ( $i % 7 ) == 0 )
                        {
                            $html .= '<div class="bs-callout bs-callout-default ripend middle">';
                            $html .= '<h4></h4>';
                            $html .= '</div>';
                        }
                        elseif ( ( $i % 7 ) == 1 )
                        {
                            $html .= '<div class="bs-callout bs-callout-default ripstart middle">';
                            $html .= '<h4>' . $termin->ter_title . '</h4>';
                            $html .= '</div>';
                        }
                        elseif($i - $startday === 0)
                        {
                            $html .= '<div class="bs-callout bs-callout-default middle">';
                            $html .= '<h4>' . $termin->ter_title . '</h4>';
                            $html .= '</div>';
                        }
                        else
                        {
                            $html .= '<div class="bs-callout bs-callout-default middle">';
                            $html .= '<h4></h4>';
                            $html .= '</div>';
                        }
                    }
                }
                $html .= '</td>';
            }
            if ( ( $i % 7 ) == 7 )
            {
                $html .= "</tr>";
            }
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}
