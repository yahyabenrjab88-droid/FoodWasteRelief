using System;
using System.Threading.Tasks;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Helpers;

public static class ActivityLogHelper
{
    public static async Task LogActivityAsync(int userId, string role, string username, string action, string details)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            const string sql = @"INSERT INTO activity_logs(user_id, user_role, username, action, details, created_at)
                                 VALUES(@user_id, @user_role, @username, @action, @details, NOW())";
            await using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@user_id", userId);
            cmd.Parameters.AddWithValue("@user_role", role ?? "");
            cmd.Parameters.AddWithValue("@username", username ?? "");
            cmd.Parameters.AddWithValue("@action", action ?? "");
            cmd.Parameters.AddWithValue("@details", details ?? "");
            await cmd.ExecuteNonQueryAsync();
        }
        catch
        {
            // Keep activity logging non-blocking for user actions.
        }
    }
}
