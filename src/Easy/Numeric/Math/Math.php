<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Numeric\Math;

/**
 * Provides constants and static methods for trigonometric, logarithmic, and other common mathematical functions.
 *
 * @since 2.0.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Math
{
    /**
     * Represents the ratio of the circumference of a circle to its diameter, specified by the constant, π.
     *
     * @access public
     * @property-read
     *
     */

    const PI = 3.1415926535898;

    /**
     * Represents the natural logarithmic base, specified by the constant, e.
     *
     * @access public
     * @property-read
     */
    const E = 2.71828;

    /**
     * Returns the absolute value
     *
     * @param int $value A number in the range minValue≤value≤maxValue.
     *
     * @return int A single-precision floating-point number, x, such that 0 ≤ x ≤maxValue.
     */
    public static function abs($value)
    {
        return abs($value);
    }

    /**
     * Returns the angle whose cosine is the specified number.
     *
     * @param int $d A number representing a cosine, where -1 ≤d≤ 1.
     *
     * @return int An angle, θ, measured in radians, such that 0 ≤θ≤π -or- System.double.NaN if d < -1 or d > 1.
     */
    public static function acos($d)
    {
        return acos($d);
    }

    /*
     * Returns the angle whose sine is the specified number.
     *
     * @param d: A number representing a sine, where -1 ≤d≤ 1.
     *
     * @return An angle, θ, measured in radians, such that -π/2 ≤θ≤π/2  -or-  System.double.NaN if d < -1 or d > 1.
     */

    public static function asin($d)
    {
        return asin($d);
    }

    /*
     * Returns the angle whose tangent is the specified number.
     *
     * @param d: A number representing a tangent.
     *
     * @return An angle, θ, measured in radians, such that -π/2 ≤θ≤π/2. -or-  System.Double.NaN if d equals System.Double.NaN, -π/2 rounded to double precision (-1.5707963267949) if d equals System.Double.NegativeInfinity, or π/2 rounded to double precision (1.5707963267949) if d equals System.Double.PositiveInfinity.
     */

    public static function atan($d)
    {
        return atan($d);
    }

    /*
     * Returns the angle whose tangent is the quotient of two specified numbers.
     *
     * @param y: The y coordinate of a point.
     * @param x: The x coordinate of a point.
     *
     * @return An angle, θ, measured in radians, such that -π≤θ≤π, and tan(θ) = y / x, where (x, y) is a point in the Cartesian plane
     */

    public static function atan2($y, $x)
    {
        return atan2($y, $x);
    }

    /*
     * Produces the full product of two 32-bit numbers.
     *
     * @param a: The first int to multiply.
     * @param b: The first int to multiply.
     *
     * @return The int containing the product of the specified numbers.
     */

    public static function bigMul($a, $b)
    {
        return $a * $b;
    }

    /*
     * Returns the smallest integer greater than or equal to the specified decimal number.
     *
     * @param d: A decimal number.
     *
     * @return The smallest integer greater than or equal to d. 
     */

    public static function ceiling($d)
    {
        return ceil($d);
    }

    /*
     * Returns the cosine of the specified angle.
     *
     * @param d: An angle, measured in radians.
     *
     * @return The cosine of d.
     */

    public static function cos($d)
    {
        return cos($d);
    }

    /*
     * Returns the hyperbolic cosine of the specified angle.
     *
     * @param value: An angle, measured in radians.
     *
     * @return The hyperbolic cosine of value. If value is equal to System.Double.NegativeInfinity or System.Double.PositiveInfinity, System.Double.PositiveInfinity is returned. If value is equal to System.Double.NaN, System.Double.NaN is returned.
     */

    public static function cosh($value)
    {
        return cosh($value);
    }

    /*
     * Calculates the quotient of two 32-bit signed integers and also returns the remainder in an output parameter.
     *
     * @param a: The int that contains the dividend.
     * @param b: The int that contains the divisor.
     * @param result: The int that receives the remainder. 
     *
     * @return The int containing the quotient of the specified numbers.
     */

    public static function divRem($a, $b, &$result)
    {
        if ($b == 0) {
            throw new \LogicException("b is zero.");
        }

        $result = gmp_div_q($a, $b);
    }

    /*
     * Returns e raised to the specified power.
     *
     * @param d: A number specifying a power.
     *
     * @return The number e raised to the power d. If d equals System.Double.NaN or System.Double.PositiveInfinity, that value is returned. If d equals System.Double.NegativeInfinity, 0 is returned.
     */

    public static function exp($d)
    {
        return exp($d);
    }

    /*
     * Returns the largest integer less than or equal to the specified decimal number.
     *
     * @param d: A decimal number.
     *
     * @return The largest integer less than or equal to d. 
     */

    public static function floor($d)
    {
        return floor($d);
    }

    /*
     * Returns the remainder resulting from the division of a specified number by another specified number.
     *
     * @param x: A dividend.
     * @param y: A divisor.
     *
     * @return A number equal to x - (y Q), where Q is the quotient of x / y rounded to the nearest integer (if x / y falls halfway between two integers, the even integer is returned).
     */

    public static function ieeeReminder($x, $y)
    {
        return $x - ($y * $this->round($x / $y));
    }

    /*
     * Returns the natural (base e) logarithm of a specified number.
     *
     * @param d: A number whose logarithm is to be found.
     *
     * @return Sign of d Returns  The natural logarithm of d; that is, ln d, or log ed Zero -or- System.Double.NegativeInfinity Negative -or- System.Double.NaN If d is equal to System.Double.NaN, returns System.Double.NaN. If d is equal to System.Double.PositiveInfinity, returns System.Double.PositiveInfinity.
     */

    public static function log($d)
    {
        return log($d);
    }

    /*
     * Returns the base 10 logarithm of a specified number.
     *
     * @param d: A number whose logarithm is to be found.
     *
     * @return  The base 10 log of d; that is, log 10d. 
     */

    public static function log10($d)
    {
        return log($d, 10);
    }

    /*
     * Returns the larger of two values.
     *
     * @param val1: The first of two values to compare.
     * @param val2: The second of two values to compare.
     *
     * @return Parameter val1 or val2, whichever is larger.
     */

    public static function max($val1, $val2)
    {
        return max(array($val1, $val2));
    }

    /*
     * Returns the smaller of two values.
     *
     * @param val1: The first of two values to compare.
     * @param val2: The second of two values to compare.
     *
     * @return Parameter val1 or val2, whichever is smaller.
     */

    public static function min($val1, $val2)
    {
        return min(array($val1, $val2));
    }

    /*
     * Returns a specified number raised to the specified power.
     *
     * @param x: A double-precision floating-point number to be raised to a power.
     * @param y: A double-precision floating-point number that specifies a power.
     *
     * @return The number x raised to the power y.
     */

    public static function pow($x, $y)
    {
        return pow($x, $y);
    }

    /*
     * Rounds a decimal value to the nearest integer.
     *
     * @param d: A decimal number to be rounded.
     * @param decimals: The number of decimal places in the return value.
     * @param mode: Specification for how to round d if it is midway between two other numbers.
     *
     * @return The integer nearest parameter d. If the fractional component of d is halfway between two integers, one of which is even and the other odd, then the even number is returned.
     */

    public static function round($d, $decimals = 0, $mode = PHP_ROUND_HALF_UP)
    {
        return round($d, $decimals, $mode);
    }

    /*
     * Returns a value indicating the sign
     *
     * @param value: A signed number.
     *
     * @return A number indicating the sign of value.
     */

    public static function sign($value)
    {
        if (is_numeric($value)) {
            if ($value < 0)
                return -1;
            if ($value > 0)
                return 1;
        }
        return 0;
    }

    /*
     * Returns the hyperbolic sine of the specified angle.
     *
     * @param value: An angle, measured in radians.
     *
     * @return The hyperbolic sine of value. If value is equal to System.Double.NegativeInfinity, System.Double.PositiveInfinity, or System.Double.NaN, this method returns a System.Double equal to value.
     */

    public static function sinh($value)
    {
        return sinh($value);
    }

    /*
     * Returns the square root of a specified number.
     *
     * @param d: A number. 
     *
     * @return Value of d Returns Zero, or positive The positive square root of d.
     */

    public static function sqrt($value)
    {
        return sqrt($value);
    }

    /*
     * Returns the tangent of the specified angle.
     * 
     * @param a: An angle, measured in radians. 
     * 
     * @return The tangent of a. If a is equal to System.Double.NaN, System.Double.NegativeInfinity, or System.Double.PositiveInfinity, this method returns System.Double.NaN.
     */

    public static function tan($a)
    {
        return tan($a);
    }

    /*
     * Returns the hyperbolic tangent of the specified angle.
     *
     * @param value: An angle, measured in radians.
     *
     * @return The hyperbolic tangent of value. If value is equal to System.Double.NegativeInfinity, this method returns -1. If value is equal to System.Double.PositiveInfinity, this method returns 1. If value is equal to System.Double.NaN, this method returns System.Double.NaN.
     */

    public static function tanh($value)
    {
        return tanh($value);
    }

    /*
     * Calculates the integral part of a specified decimal number.
     *
     * @param d: A number to truncate.
     *
     * @return Return Values: The integral part of d; that is, the number that remains after any fractional digits have been discarded.
     */

    public static function trucante($d)
    {
        return false;
    }

}
