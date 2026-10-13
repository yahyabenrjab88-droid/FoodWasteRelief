using System;
using System.Globalization;
using System.Windows.Data;

namespace FoodWasteRelief.Helpers;

public class PriceToStringConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        if (value is decimal d)
            return $"{d:F2} TND";
        if (decimal.TryParse(value?.ToString(), out var parsed))
            return $"{parsed:F2} TND";
        return "0.00 TND";
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) => Binding.DoNothing;
}
