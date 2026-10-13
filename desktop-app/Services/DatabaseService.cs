using System.Configuration;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public static class DatabaseService
{
    /// <summary>
    /// SDK-style WPF apps need System.Configuration.ConfigurationManager; if App.config is missing
    /// or not merged at runtime, fall back to the same default as App.config.
    /// </summary>
    private const string FallbackConnectionString =
        "Server=127.0.0.1;Port=3306;Database=foodwaste_db;Uid=root;Pwd=;";

    private static readonly string ConnectionString = ResolveConnectionString();

    private static string ResolveConnectionString()
    {
        var fromConfig = ConfigurationManager.ConnectionStrings["FoodWasteDB"]?.ConnectionString;
        if (!string.IsNullOrWhiteSpace(fromConfig))
            return fromConfig.Trim();
        return FallbackConnectionString;
    }

    public static MySqlConnection GetOpenConnection()
    {
        var conn = new MySqlConnection(ConnectionString);
        conn.Open();
        return conn;
    }

    public static async Task<MySqlConnection> GetConnectionAsync()
    {
        var conn = new MySqlConnection(ConnectionString);
        await conn.OpenAsync();
        return conn;
    }
}

