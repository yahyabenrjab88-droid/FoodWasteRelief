using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class ActivityLogService
{
    /// <summary>Admin: recent platform-wide rows. Others: rows for the given user only.</summary>
    public async Task<List<ActivityLogEntry>> GetRecentAsync(bool adminViewAll, int userId, int limit = 400)
    {
        if (limit < 1) limit = 1;
        if (limit > 2000) limit = 2000;

        var list = new List<ActivityLogEntry>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = adminViewAll
            ? @"SELECT created_at, username, user_role, action, details
                FROM activity_logs
                ORDER BY created_at DESC, id DESC
                LIMIT @lim"
            : @"SELECT created_at, username, user_role, action, details
                FROM activity_logs
                WHERE user_id = @uid
                ORDER BY created_at DESC, id DESC
                LIMIT @lim";

        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@lim", limit);
        if (!adminViewAll)
            cmd.Parameters.AddWithValue("@uid", userId);

        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new ActivityLogEntry
            {
                CreatedAt = Convert.ToDateTime(reader["created_at"]),
                Username = Convert.ToString(reader["username"]) ?? "",
                UserRole = Convert.ToString(reader["user_role"]) ?? "",
                Action = Convert.ToString(reader["action"]) ?? "",
                Details = Convert.ToString(reader["details"]) ?? ""
            });
        }

        return list;
    }
}
