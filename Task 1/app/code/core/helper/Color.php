<?php
/**
 * Class Color - offers functionality related to colors.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Color {
        // HEX - Hexadecimal
        const COLOR_MODEL_HEX                   = 'hex';
        // HSL - Hue, Saturation and Lightness
        const COLOR_MODEL_HSL                   = 'hsl';
        // RGB - Red, Green and Blue
        const COLOR_MODEL_RGB                   = 'rgb';

        /* General */

        /**
         * Calculate the negative for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Negative color
         */
        public static function negative($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                $r = $g = $b = null;
                switch ($model) {
                        case self::COLOR_MODEL_RGB:
                                list($r, $g, $b) = $color;
                                break;
                        case self::COLOR_MODEL_HEX:
                                list($r, $g, $b) = self::hex2rgb($color);
                                break;
                        case self::COLOR_MODEL_HSL:
                                list($r, $g, $b) = self::hsl2rgb($color[0], $color[1], $color[2]);
                                break;
                }

                $r = (255 - $r);
                $g = (255 - $g);
                $b = (255 - $b);

                switch ($model) {
                        case self::COLOR_MODEL_RGB:
                                return array($r, $g, $b);
                        case self::COLOR_MODEL_HEX:
                                return self::rgb2hex($r, $g, $b, $prependHash);
                        case self::COLOR_MODEL_HSL:
                                return self::rgb2hsl($r, $g, $b);
                }
        }

        /* Harmonies */

        /**
         * Calculate the complementary harmony color for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Complementary color
         */
        public static function complementary($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                $complementary = self::harmony($color, array(180), $model, $prependHash);

                return ($complementary ? reset($complementary) : null);
        }


        /**
         * Calculate the analogous harmony colors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Analogous colors
         */
        public static function analogous($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(30, 330), $model, $prependHash);
        }

        /**
         * Calculate the triad harmonycolors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Triad colors
         */
        public static function triad($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(120, 240), $model, $prependHash);
        }

        /**
         * Calculate the split complementary harmony colors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Split complementary colors
         */
        public static function splitComplementary($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(150, 210), $model, $prependHash);
        }

        /**
         * Calculate the tetradic harmony colors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Tetradic colors
         */
        public static function tetradic($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(60, 180, 240), $model, $prependHash);
        }

        /**
         * Calculate the square harmony colors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Square colors
         */
        public static function square($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(90, 180, 270), $model, $prependHash);
        }

        /**
         * Calculate the harmony color wheel for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Color wheel
         */
        public static function wheel($color, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                return self::harmony($color, array(0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330), $model, $prependHash);
        }

        /**
         * Calculate harmony colors for a given color.
         *
         * @param       mixed           $color          Original color
         * @param       mixed           $degrees        Color wheel degrees
         * @param       string          $model          Color model (optional)
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      mixed                           Harmony colors
         */
        public static function harmony($color, $degrees, $model = self::COLOR_MODEL_HEX, $prependHash = true) {
                $h = $s = $l = null;
                switch ($model) {
                        case self::COLOR_MODEL_RGB:
                                list($h, $s, $l) = self::rgb2hsl($color[0], $color[1], $color[2]);
                                break;
                        case self::COLOR_MODEL_HEX:
                                list($h, $s, $l) = self::hex2hsl($color);
                                break;
                        case self::COLOR_MODEL_HSL:
                                list($h, $s, $l) = $color;
                                break;
                }

                $rgb = self::hslShift2rgb($h, $s, $l, $degrees);

                $harmony = array();
                switch ($model) {
                        case self::COLOR_MODEL_RGB:
                                $harmony = $rgb;
                                break;
                        case self::COLOR_MODEL_HEX:
                                foreach ($rgb as $color) {
                                        $harmony[] = self::rgb2hex($color[0], $color[1], $color[2], $prependHash);
                                }
                                break;
                        case self::COLOR_MODEL_HSL:
                                foreach ($rgb as $color) {
                                        $harmony[] = self::rgb2hsl($color[0], $color[1], $color[2]);
                                }
                                break;
                }

                return $harmony;
        }

        /* Conversion */

        /**
         * Convert a color from the HEX color model to the RGB color model.
         *
         * @param       string          $hex            HEX color
         * @return      array                           RGB color
         */
        public static function hex2rgb($hex) {
                $hex = str_replace('#', '', $hex);
                if (strlen($hex) != 6) $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);

                return array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        }

        /**
         * Convert a color from the RGB color model to the HEX color model.
         *
         * @param       string          $r              Red color
         * @param       string          $g              Green color
         * @param       string          $b              Blue color
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      string                          HEX color
         */
        public static function rgb2hex($r, $g, $b, $prependHash = true) {
                return ($prependHash ? '#' : '') . sprintf('%02X', $r) . sprintf('%02X', $g) . sprintf('%02X', $b);
        }

        /**
         * Convert a color from the HEX color model to the HSL color model.
         *
         * @param       string          $hex            HEX color
         * @return      array                           HSL color
         */
        public static function hex2hsl($hex) {
                list($r, $g, $b) = self::hex2rgb($hex);

                return self::rgb2hsl($r, $g, $b);
        }

        /**
         * Convert a color from the HSL color model to the HEX color model.
         *
         * @param       string          $h              Hue
         * @param       string          $s              Saturation
         * @param       string          $l              Lightness
         * @param       boolean         $prependHash    True to prepend a hash to HEX output (optional)
         * @return      string                          HEX color
         */
        public static function hsl2hex($h, $s, $l, $prependHash = true) {
                list($r, $g, $b) = self::hsl2rgb($h, $s, $l);

                return self::rgb2hex($r, $g, $b, $prependHash);
        }

        /**
         * Convert a color from the RGB color model to the HSL color model.
         *
         * @param       string          $r              Red color
         * @param       string          $g              Green color
         * @param       string          $b              Blue color
         * @return      string                          HSL color
         */
        public static function rgb2hsl($r, $g, $b) {
                $r /= 255;
                $g /= 255;
                $b /= 255;

                $min = min($r, $g, $b);
                $max = max($r, $g, $b);
                $del = ($max - $min);

                $l = (($max + $min) / 2);
                if ($del === 0) {
                        $h = $s = 0;
                } else {
                        $s = ($del / ($l < 0.5 ? ($max + $min) : (2 - $max - $min)));

                        $delR = (((($max - $r) / 6) + ($del / 2)) / $del);
                        $delG = (((($max - $g) / 6) + ($del / 2)) / $del);
                        $delB = (((($max - $b) / 6) + ($del / 2)) / $del);

                        if ($r === $max) {
                                $h = ($b - $delG);
                        } elseif ($g === $max) {
                                $h = ((1 / 3) + $delR - $delB);
                        } elseif ($b === $max) {
                                $h = ((2 / 3) + $delG - $delR);
                        }

                        if ($h < 0) $h += 1;
                        if ($h > 1) $h -= 1;
                }

                return array(round($h * 360), round($s * 100), round($l * 100));
        }

        /**
         * Convert a color from the HSL color model to the RGB color model.
         *
         * @param       string          $h              Hue
         * @param       string          $s              Saturation
         * @param       string          $l              Lightness
         * @return      string                          RGB color
         */
        public static function hsl2rgb($h, $s, $l) {
                $h /= 360;
                $s /= 100;
                $l /= 100;

                $r = $g = $b = $l;
                $v = ($l <= 0.5) ? ($l * (1 + $s)) : ($l + $s - $l * $s);
                if ($v > 0) {
                      $m = ($l + $l - $v);
                      $sv = (($v - $m ) / $v);
                      $h *= 6;
                      $sextant = floor($h);
                      $fract = ($h - $sextant);
                      $vsf = ($v * $sv * $fract);
                      $mid1 = ($m + $vsf);
                      $mid2 = ($v - $vsf);

                      switch ($sextant) {
                            case 0:
                                  $r = $v;
                                  $g = $mid1;
                                  $b = $m;
                                  break;
                            case 1:
                                  $r = $mid2;
                                  $g = $v;
                                  $b = $m;
                                  break;
                            case 2:
                                  $r = $m;
                                  $g = $v;
                                  $b = $mid1;
                                  break;
                            case 3:
                                  $r = $m;
                                  $g = $mid2;
                                  $b = $v;
                                  break;
                            case 4:
                                  $r = $mid1;
                                  $g = $m;
                                  $b = $v;
                                  break;
                            case 5:
                                  $r = $v;
                                  $g = $m;
                                  $b = $mid2;
                                  break;
                      }
                }

                return array(floor($r * 255), round($g * 255), round($b * 255));
        }

        /**
         * Shift HSL values within the color wheel using the given degrees
         * and convert to the RGB color model.
         *
         * @param       string          $h              Hue
         * @param       string          $s              Saturation
         * @param       string          $l              Lightness
         * @param       mixed           $degrees        Color wheel degrees
         * @return      array                           RGB colors
         */
        private static function hslShift2rgb($h, $s, $l, $degrees) {
                if (!is_array($degrees)) $degrees = array($degrees);

                $colors = array();
                if ($s === 0) {
                        $colors = array_fill(0, count($degrees), ($l * 255));
                } else {
                        foreach ($degrees as $degree) {
                                $hN = ($h + $degree);
                                if ($hN > 360) $hN -= 360;

                                $colors[] = self::hsl2rgb($hN, $s, $l);
                        }
                }

                return $colors;
        }
}