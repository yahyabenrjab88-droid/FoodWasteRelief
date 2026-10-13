using System;
using System.Collections.Generic;
using System.Data.Common;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class UserService
{
    private static User MapUser(DbDataReader reader)
    {
        return new User
        {
            Id = Convert.ToInt32(reader["id"]),
            Email = Convert.ToString(reader["email"]) ?? "",
            Password = Convert.ToString(reader["password"]) ?? "",
            Role = Convert.ToString(reader["role"]) ?? "client",
            Nom = Convert.ToString(reader["nom"]) ?? "",
            Prenom = Convert.ToString(reader["prenom"]) ?? "",
            Telephone = Convert.ToString(reader["telephone"]) ?? "",
            CreatedAt = Convert.ToDateTime(reader["created_at"])
        };
    }

    private static bool VerifyPassword(string plainPassword, string storedPassword)
    {
        if (string.IsNullOrWhiteSpace(storedPassword))
            return false;

        return string.Equals(plainPassword.Trim(), storedPassword.Trim(), StringComparison.Ordinal);
    }

    public async Task<List<User>> GetAllAsync()
    {
        var users = new List<User>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, email, password, role, nom, prenom, telephone, created_at
                             FROM users ORDER BY created_at DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            users.Add(MapUser(reader));
        return users;
    }

    public async Task<User?> GetByIdAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, email, password, role, nom, prenom, telephone, created_at
                             FROM users WHERE id = @id LIMIT 1";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        await using var reader = await cmd.ExecuteReaderAsync();
        return await reader.ReadAsync() ? MapUser(reader) : null;
    }

    public async Task<User?> GetByEmailAsync(string email)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, email, password, role, nom, prenom, telephone, created_at
                             FROM users WHERE LOWER(TRIM(email)) = LOWER(TRIM(@email)) LIMIT 1";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@email", email.Trim());
        await using var reader = await cmd.ExecuteReaderAsync();
        return await reader.ReadAsync() ? MapUser(reader) : null;
    }

    public async Task<User?> AuthenticateAsync(string email, string password)
    {
        var user = await GetByEmailAsync(email);
        if (user is null)
            return null;

        return VerifyPassword(password, user.Password) ? user : null;
    }

    public async Task<int> CreateAsync(User user, string plainPassword)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"INSERT INTO users(email, password, role, nom, prenom, telephone, created_at)
                             VALUES (@email, @password, @role, @nom, @prenom, @telephone, NOW());
                             SELECT LAST_INSERT_ID();";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@email", user.Email.Trim());
        cmd.Parameters.AddWithValue("@password", plainPassword);
        cmd.Parameters.AddWithValue("@role", user.Role.Trim().ToLowerInvariant());
        cmd.Parameters.AddWithValue("@nom", user.Nom.Trim());
        cmd.Parameters.AddWithValue("@prenom", user.Prenom.Trim());
        cmd.Parameters.AddWithValue("@telephone", user.Telephone.Trim());
        var idObj = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(idObj);
    }

    public async Task<bool> UpdateAsync(User user)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"UPDATE users
                             SET email=@email, role=@role, nom=@nom, prenom=@prenom, telephone=@telephone
                             WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", user.Id);
        cmd.Parameters.AddWithValue("@email", user.Email.Trim());
        cmd.Parameters.AddWithValue("@role", user.Role.Trim().ToLowerInvariant());
        cmd.Parameters.AddWithValue("@nom", user.Nom.Trim());
        cmd.Parameters.AddWithValue("@prenom", user.Prenom.Trim());
        cmd.Parameters.AddWithValue("@telephone", user.Telephone.Trim());
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> UpdatePasswordAsync(int userId, string newPlain)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "UPDATE users SET password=@password WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", userId);
        cmd.Parameters.AddWithValue("@password", newPlain);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "DELETE FROM users WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<int> GetCountByRoleAsync(string role)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = "SELECT COUNT(*) FROM users WHERE role=@role";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@role", role.Trim().ToLowerInvariant());
        var result = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(result);
    }
}
