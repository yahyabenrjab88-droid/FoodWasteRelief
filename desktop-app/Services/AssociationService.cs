using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class AssociationService
{
    public async Task CreatePendingAssociationForUserAsync(int userId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = @"INSERT INTO `associations` (`user_id`, `nom`, `ville`, `telephone`, `status`)
                            VALUES (@uid, 'Nouvelle association', 'À compléter', 'NON_RENSEIGNÉ', 'en_attente')";
        cmd.Parameters.AddWithValue("@uid", userId);
        await cmd.ExecuteNonQueryAsync();
    }

    public async Task<int?> GetAssociationIdByUserIdAsync(int userId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT `id` FROM `associations` WHERE `user_id` = @userId LIMIT 1";
        cmd.Parameters.AddWithValue("@userId", userId);
        var obj = await cmd.ExecuteScalarAsync();
        if (obj is null || obj is System.DBNull) return null;
        return System.Convert.ToInt32(obj);
    }

    public async Task<List<Association>> GetAllAsync()
    {
        var list = new List<Association>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "SELECT id, user_id, nom, ville, telephone, status FROM associations ORDER BY id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Association
            {
                Id = System.Convert.ToInt32(reader["id"]),
                UserId = reader["user_id"] is System.DBNull ? 0 : System.Convert.ToInt32(reader["user_id"]),
                Nom = System.Convert.ToString(reader["nom"]) ?? "",
                Ville = System.Convert.ToString(reader["ville"]) ?? "",
                Telephone = System.Convert.ToString(reader["telephone"]) ?? "",
                Status = System.Convert.ToString(reader["status"]) ?? "en_attente"
            });
        }

        return list;
    }

    public async Task<int> GetPendingCountAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("SELECT COUNT(*) FROM associations WHERE status='en_attente'", conn);
        var result = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(result);
    }

    public async Task<bool> ApproveAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("UPDATE associations SET status='valide' WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> RejectAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("UPDATE associations SET status='refuse' WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("DELETE FROM associations WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }
}
