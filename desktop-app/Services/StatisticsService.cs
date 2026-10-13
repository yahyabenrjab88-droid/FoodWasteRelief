using System.Threading.Tasks;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class StatisticsService
{
    public async Task<int> GetUsersCountAsync()
    {
        await using var conn = DatabaseService.GetOpenConnection();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT COUNT(*) FROM users";
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(obj);
    }

    public async Task<int> GetPendingApprovalsAsync()
    {
        await using var conn = DatabaseService.GetOpenConnection();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = @"SELECT
                              (SELECT COUNT(*) FROM business WHERE status='en_attente') +
                              (SELECT COUNT(*) FROM associations WHERE status='en_attente') AS pending";
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(obj);
    }

    public async Task<int> GetProductsCountAsync()
    {
        await using var conn = DatabaseService.GetOpenConnection();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT COUNT(*) FROM produits";
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(obj);
    }

    public async Task<int> GetTotalDonatedMealsAsync()
    {
        await using var conn = DatabaseService.GetOpenConnection();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT COALESCE(SUM(quantite), 0) FROM donations";
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(obj);
    }

    public async Task<decimal> GetDeliveredRevenueAsync(int? businessId = null)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = "SELECT COALESCE(SUM(total),0) FROM commandes WHERE status='livree'";
        if (businessId.HasValue)
            sql += " AND business_id=@businessId";
        await using var cmd = new MySqlCommand(sql, conn);
        if (businessId.HasValue)
            cmd.Parameters.AddWithValue("@businessId", businessId.Value);
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToDecimal(obj);
    }

    public async Task<int> GetCountAsync(string table)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand($"SELECT COUNT(*) FROM {table}", conn);
        var obj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(obj);
    }
}

