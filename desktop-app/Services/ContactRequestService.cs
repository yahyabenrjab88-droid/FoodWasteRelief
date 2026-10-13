using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class ContactRequestService
{
    public async Task<int> CreateAsync(ContactRequest request)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"INSERT INTO contact_requests(sender_id, sender_role, sender_name, subject, body, sent_at, is_read)
                             VALUES(@sender_id, @sender_role, @sender_name, @subject, @body, NOW(), 0);
                             SELECT LAST_INSERT_ID();";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@sender_id", request.SenderId);
        cmd.Parameters.AddWithValue("@sender_role", request.SenderRole);
        cmd.Parameters.AddWithValue("@sender_name", request.SenderName);
        cmd.Parameters.AddWithValue("@subject", request.Subject);
        cmd.Parameters.AddWithValue("@body", request.Body);
        var result = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(result);
    }

    public async Task<List<ContactRequest>> GetAllAsync()
    {
        var list = new List<ContactRequest>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, sender_id, sender_role, sender_name, subject, body, sent_at, is_read
                             FROM contact_requests
                             ORDER BY is_read ASC, sent_at DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new ContactRequest
            {
                Id = Convert.ToInt32(reader["id"]),
                SenderId = Convert.ToInt32(reader["sender_id"]),
                SenderRole = Convert.ToString(reader["sender_role"]) ?? "",
                SenderName = Convert.ToString(reader["sender_name"]) ?? "",
                Subject = Convert.ToString(reader["subject"]) ?? "",
                Body = Convert.ToString(reader["body"]) ?? "",
                SentAt = Convert.ToDateTime(reader["sent_at"]),
                IsRead = Convert.ToInt32(reader["is_read"]) == 1
            });
        }
        return list;
    }

    public async Task<List<ContactRequest>> GetBySenderAsync(int senderId)
    {
        var list = new List<ContactRequest>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, sender_id, sender_role, sender_name, subject, body, sent_at, is_read
                             FROM contact_requests
                             WHERE sender_id = @sender_id
                             ORDER BY sent_at DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@sender_id", senderId);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new ContactRequest
            {
                Id = Convert.ToInt32(reader["id"]),
                SenderId = Convert.ToInt32(reader["sender_id"]),
                SenderRole = Convert.ToString(reader["sender_role"]) ?? "",
                SenderName = Convert.ToString(reader["sender_name"]) ?? "",
                Subject = Convert.ToString(reader["subject"]) ?? "",
                Body = Convert.ToString(reader["body"]) ?? "",
                SentAt = Convert.ToDateTime(reader["sent_at"]),
                IsRead = Convert.ToInt32(reader["is_read"]) == 1
            });
        }
        return list;
    }

    public async Task<int> GetUnreadCountAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("SELECT COUNT(*) FROM contact_requests WHERE is_read=0", conn);
        var result = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(result);
    }

    public async Task MarkAsReadAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("UPDATE contact_requests SET is_read=1 WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        await cmd.ExecuteNonQueryAsync();
    }
}
