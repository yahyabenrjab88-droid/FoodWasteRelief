using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class BusinessService
{
    /// <summary>Called after a new user registers as business — pending validation by admin.</summary>
    public async Task CreatePendingBusinessForUserAsync(int userId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = @"INSERT INTO `business` (`user_id`, `nom_business`, `localisation`, `ville`, `patente`, `status`)
                            VALUES (@uid, 'Nouveau commerce', 'À compléter', 'À compléter', 'NON_RENSEIGNÉE', 'en_attente')";
        cmd.Parameters.AddWithValue("@uid", userId);
        await cmd.ExecuteNonQueryAsync();
    }

    public async Task<int?> GetBusinessIdByUserIdAsync(int userId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT `id` FROM `business` WHERE `user_id` = @userId LIMIT 1";
        cmd.Parameters.AddWithValue("@userId", userId);
        var obj = await cmd.ExecuteScalarAsync();
        if (obj is null || obj is System.DBNull) return null;
        return System.Convert.ToInt32(obj);
    }

    public async Task<List<Business>> GetPendingAsync()
    {
        var list = new List<Business>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = conn.CreateCommand();
        cmd.CommandText = @"SELECT b.id, b.user_id, b.nom_business, b.localisation, b.ville, b.patente, b.status,
                                   u.nom, u.prenom, u.email, u.telephone
                            FROM business b
                            JOIN users u ON u.id = b.user_id
                            WHERE b.status = 'en_attente'
                            ORDER BY b.id DESC";
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Business
            {
                Id = System.Convert.ToInt32(reader["id"]),
                UserId = System.Convert.ToInt32(reader["user_id"]),
                NomBusiness = System.Convert.ToString(reader["nom_business"]) ?? "",
                Localisation = System.Convert.ToString(reader["localisation"]) ?? "",
                Ville = System.Convert.ToString(reader["ville"]) ?? "",
                Patente = System.Convert.ToString(reader["patente"]) ?? "",
                Status = System.Convert.ToString(reader["status"]) ?? "en_attente",
                Owner = new User
                {
                    Id = System.Convert.ToInt32(reader["user_id"]),
                    Nom = System.Convert.ToString(reader["nom"]) ?? "",
                    Prenom = System.Convert.ToString(reader["prenom"]) ?? "",
                    Email = System.Convert.ToString(reader["email"]) ?? "",
                    Telephone = System.Convert.ToString(reader["telephone"]) ?? "",
                    Role = "business"
                }
            });
        }
        return list;
    }

    public async Task<List<Business>> GetAllWithOwnerAsync()
    {
        var list = new List<Business>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT b.id, b.user_id, b.nom_business, b.localisation, b.ville, b.patente, b.status,
                                    u.nom, u.prenom, u.email, u.telephone
                             FROM business b
                             LEFT JOIN users u ON u.id = b.user_id
                             ORDER BY b.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Business
            {
                Id = System.Convert.ToInt32(reader["id"]),
                UserId = System.Convert.ToInt32(reader["user_id"]),
                NomBusiness = System.Convert.ToString(reader["nom_business"]) ?? "",
                Localisation = System.Convert.ToString(reader["localisation"]) ?? "",
                Ville = System.Convert.ToString(reader["ville"]) ?? "",
                Patente = System.Convert.ToString(reader["patente"]) ?? "",
                Status = System.Convert.ToString(reader["status"]) ?? "en_attente",
                Owner = new User
                {
                    Id = System.Convert.ToInt32(reader["user_id"]),
                    Nom = System.Convert.ToString(reader["nom"]) ?? "",
                    Prenom = System.Convert.ToString(reader["prenom"]) ?? "",
                    Email = System.Convert.ToString(reader["email"]) ?? "",
                    Telephone = System.Convert.ToString(reader["telephone"]) ?? "",
                    Role = "business"
                }
            });
        }

        return list;
    }

    public async Task<List<Business>> GetAllAsync() => await GetAllWithOwnerAsync();

    public async Task<Business?> GetByIdAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT b.id, b.user_id, b.nom_business, b.localisation, b.ville, b.patente, b.status,
                                    u.nom, u.prenom, u.email, u.telephone
                             FROM business b
                             LEFT JOIN users u ON u.id = b.user_id
                             WHERE b.id = @id
                             LIMIT 1";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        await using var reader = await cmd.ExecuteReaderAsync();
        if (!await reader.ReadAsync())
            return null;

        return new Business
        {
            Id = System.Convert.ToInt32(reader["id"]),
            UserId = System.Convert.ToInt32(reader["user_id"]),
            NomBusiness = System.Convert.ToString(reader["nom_business"]) ?? "",
            Localisation = System.Convert.ToString(reader["localisation"]) ?? "",
            Ville = System.Convert.ToString(reader["ville"]) ?? "",
            Patente = System.Convert.ToString(reader["patente"]) ?? "",
            Status = System.Convert.ToString(reader["status"]) ?? "en_attente",
            Owner = new User
            {
                Id = System.Convert.ToInt32(reader["user_id"]),
                Nom = System.Convert.ToString(reader["nom"]) ?? "",
                Prenom = System.Convert.ToString(reader["prenom"]) ?? "",
                Email = System.Convert.ToString(reader["email"]) ?? "",
                Telephone = System.Convert.ToString(reader["telephone"]) ?? "",
                Role = "business"
            }
        };
    }

    public async Task<Business?> GetByUserIdAsync(int userId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, user_id, nom_business, localisation, ville, patente, status
                             FROM business WHERE user_id=@userId LIMIT 1";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@userId", userId);
        await using var reader = await cmd.ExecuteReaderAsync();
        if (!await reader.ReadAsync())
            return null;

        return new Business
        {
            Id = System.Convert.ToInt32(reader["id"]),
            UserId = System.Convert.ToInt32(reader["user_id"]),
            NomBusiness = System.Convert.ToString(reader["nom_business"]) ?? "",
            Localisation = System.Convert.ToString(reader["localisation"]) ?? "",
            Ville = System.Convert.ToString(reader["ville"]) ?? "",
            Patente = System.Convert.ToString(reader["patente"]) ?? "",
            Status = System.Convert.ToString(reader["status"]) ?? "en_attente"
        };
    }

    public async Task<int> GetPendingCountAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "SELECT COUNT(*) FROM business WHERE status='en_attente'";
        await using var cmd = new MySqlCommand(sql, conn);
        var result = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(result);
    }

    public async Task<bool> ApproveAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "UPDATE business SET status='valide' WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> RejectAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "UPDATE business SET status='refuse' WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<int> CreateAsync(Business b)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"INSERT INTO business(user_id, nom_business, localisation, ville, patente, status)
                             VALUES(@user_id, @nom_business, @localisation, @ville, @patente, @status);
                             SELECT LAST_INSERT_ID();";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@user_id", b.UserId);
        cmd.Parameters.AddWithValue("@nom_business", b.NomBusiness);
        cmd.Parameters.AddWithValue("@localisation", b.Localisation);
        cmd.Parameters.AddWithValue("@ville", b.Ville);
        cmd.Parameters.AddWithValue("@patente", b.Patente);
        cmd.Parameters.AddWithValue("@status", b.Status);
        var idObj = await cmd.ExecuteScalarAsync();
        return System.Convert.ToInt32(idObj);
    }

    public async Task<bool> UpdateAsync(Business b)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"UPDATE business
                             SET user_id=@user_id, nom_business=@nom_business, localisation=@localisation,
                                 ville=@ville, patente=@patente, status=@status
                             WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", b.Id);
        cmd.Parameters.AddWithValue("@user_id", b.UserId);
        cmd.Parameters.AddWithValue("@nom_business", b.NomBusiness);
        cmd.Parameters.AddWithValue("@localisation", b.Localisation);
        cmd.Parameters.AddWithValue("@ville", b.Ville);
        cmd.Parameters.AddWithValue("@patente", b.Patente);
        cmd.Parameters.AddWithValue("@status", b.Status);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "DELETE FROM business WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }
}

