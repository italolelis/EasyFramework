<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Generics;

/**
 * Defines methods that convert the value of the implementing reference or value type to a common language runtime type that has an equivalent value.
 *
 * @since 2.0.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 */
interface ConvertibleInterface
{

    /**
     * Returns the System.TypeCode for this instance.
     *
     * @access public
     *
     * @return TypeCode The enumerated constant that is the System.TypeCode of the class or value type that implements this interface.
     */
    function getTypeCode();

    /**
     * Converts the value of this instance to an equivalent Boolean value using the specified culture-specific formatting information.
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Boolean A Boolean value equivalent to the value of this instance.
     */
    function toBoolean($provider);

    /**
     * Converts the value of this instance to an equivalent 8-bit unsigned integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Byte An 8-bit unsigned integer equivalent to the value of this instance.
     */
    function toByte($provider);

    /**
     * Converts the value of this instance to an equivalent Unicode character using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Char A Unicode character equivalent to the value of this instance.
     */
    function toChar($provider);

    /**
     * Converts the value of this instance to an equivalent System.DateAndTime using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return DateAndTime A System.DateAndTime instance equivalent to the value of this instance.
     */
    function toDateTime($provider);

    /**
     * Converts the value of this instance to an equivalent System.Decimal number using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Decimal A System.Decimal number equivalent to the value of this instance.
     */
    function toDecimal($provider);

    /**
     * Converts the value of this instance to an equivalent double-precision floating-point number using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Double A double-precision floating-point number equivalent to the value of this instance.
     */
    function toDouble($provider);

    /**
     * Converts the value of this instance to an equivalent 16-bit signed integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Int16 An 16-bit signed integer equivalent to the value of this instance.
     */
    function toInt16($provider);

    /**
     * Converts the value of this instance to an equivalent 32-bit signed integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Int32 An 32-bit signed integer equivalent to the value of this instance.
     */
    function toInt32($provider);

    /**
     * Converts the value of this instance to an equivalent 64-bit signed integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Int64 An 64-bit signed integer equivalent to the value of this instance.
     */
    function toInt64($provider);

    /**
     * Converts the value of this instance to an equivalent 8-bit signed integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return SByte An 8-bit signed integer equivalent to the value of this instance.
     */
    function toSByte($provider);

    /**
     * Converts the value of this instance to an equivalent single-precision floating-point number using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Single A single-precision floating-point number equivalent to the value of this instance.
     */
    function toSingle($provider);

    /**
     * Converts the value of this instance to an System.Object of the specified System.Type that has an equivalent value, using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param Type $conversionType The System.Type to which the value of this instance is converted.
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return Type An System.Object instance of type conversionType whose value is equivalent to the value of this instance.
     */
    function toType($conversionType, $provider);

    /**
     * Converts the value of this instance to an equivalent 16-bit unsigned integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return UInt16 An 16-bit unsigned integer equivalent to the value of this instance.
     */
    function toUInt16($provider);

    /**
     * Converts the value of this instance to an equivalent 32-bit unsigned integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return UInt32 An 32-bit unsigned integer equivalent to the value of this instance.
     */
    function toUInt32($provider);

    /**
     * Converts the value of this instance to an equivalent 64-bit unsigned integer using the specified culture-specific formatting information.
     *
     * @access public
     *
     * @param IFormatProvider $provider An System.IFormatProvider interface implementation that supplies culture-specific formatting information.
     *
     * @return UInt64 An 64-bit unsigned integer equivalent to the value of this instance.
     */
    function toUInt64($provider);
}
