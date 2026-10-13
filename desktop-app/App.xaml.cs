using System;
using System.Threading.Tasks;
using System.Windows;

namespace FoodWasteRelief;

/// <summary>
/// Interaction logic for App.xaml
/// </summary>
public partial class App : Application
{
    public App()
    {
        RegisterGlobalExceptionHandlers();
    }

    protected override void OnStartup(StartupEventArgs e)
    {
        // Keep handlers attached before framework startup creates windows/resources.
        RegisterGlobalExceptionHandlers();
        base.OnStartup(e);
    }

    private void RegisterGlobalExceptionHandlers()
    {
        DispatcherUnhandledException -= OnDispatcherUnhandledException;
        DispatcherUnhandledException += OnDispatcherUnhandledException;

        AppDomain.CurrentDomain.UnhandledException -= OnUnhandledException;
        AppDomain.CurrentDomain.UnhandledException += OnUnhandledException;

        TaskScheduler.UnobservedTaskException -= OnUnobservedTaskException;
        TaskScheduler.UnobservedTaskException += OnUnobservedTaskException;
    }

    private static void OnDispatcherUnhandledException(object? sender, System.Windows.Threading.DispatcherUnhandledExceptionEventArgs args)
    {
        MessageBox.Show(
            $"Erreur inattendue:\n{args.Exception.Message}\n\n{args.Exception.StackTrace}",
            "Crash capture",
            MessageBoxButton.OK,
            MessageBoxImage.Error);
        args.Handled = true;
    }

    private static void OnUnhandledException(object? sender, UnhandledExceptionEventArgs args)
    {
        var ex = args.ExceptionObject as Exception;
        MessageBox.Show(
            $"Erreur fatale:\n{ex?.Message ?? "Unknown"}\n\n{ex?.StackTrace}",
            "Crash capture",
            MessageBoxButton.OK,
            MessageBoxImage.Error);
    }

    private static void OnUnobservedTaskException(object? sender, UnobservedTaskExceptionEventArgs args)
    {
        MessageBox.Show(
            $"Erreur tache asynchrone:\n{args.Exception.Message}\n\n{args.Exception.StackTrace}",
            "Crash capture",
            MessageBoxButton.OK,
            MessageBoxImage.Warning);
        args.SetObserved();
    }
}

