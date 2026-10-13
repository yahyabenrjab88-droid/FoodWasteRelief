using System;
using System.Globalization;
using System.Windows.Data;
using System.Windows.Media;

namespace FoodWasteRelief.Helpers;

public class DateToExpiryBackgroundConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        if (value is not DateTime date)
            return Brushes.Transparent;

        if (date.Date < DateTime.Today)
            return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#FFEBEE"));
        if (date.Date <= DateTime.Today.AddDays(3))
            return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#FFFBEE"));
        return Brushes.Transparent;
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) => Binding.DoNothing;
}
