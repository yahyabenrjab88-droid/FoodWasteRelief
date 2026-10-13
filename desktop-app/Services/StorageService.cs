using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class StorageService
{
    public async Task<List<StorageItem>> GetByBusinessAsync(int businessId)
    {
        var list = new List<StorageItem>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                             FROM storage_items
                             WHERE business_id=@business_id
                             ORDER BY added_at DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@business_id", businessId);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new StorageItem
            {
                Id = Convert.ToInt32(reader["id"]),
                BusinessId = Convert.ToInt32(reader["business_id"]),
                ItemName = Convert.ToString(reader["item_name"]) ?? "",
                Quantity = Convert.ToDecimal(reader["quantity"]),
                Unit = Convert.ToString(reader["unit"]) ?? "",
                ExpiryDate = reader["expiry_date"] is DBNull ? null : Convert.ToDateTime(reader["expiry_date"]),
                AddedAt = Convert.ToDateTime(reader["added_at"])
            });
        }
        return list;
    }

    public async Task<int> CreateAsync(StorageItem item)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"INSERT INTO storage_items(business_id, item_name, quantity, unit, expiry_date, added_at)
                             VALUES(@business_id, @item_name, @quantity, @unit, @expiry_date, NOW());
                             SELECT LAST_INSERT_ID();";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@business_id", item.BusinessId);
        cmd.Parameters.AddWithValue("@item_name", item.ItemName);
        cmd.Parameters.AddWithValue("@quantity", item.Quantity);
        cmd.Parameters.AddWithValue("@unit", item.Unit);
        cmd.Parameters.AddWithValue("@expiry_date", item.ExpiryDate);
        var result = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(result);
    }

    public async Task<bool> UpdateAsync(StorageItem item)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"UPDATE storage_items
                             SET item_name=@item_name, quantity=@quantity, unit=@unit, expiry_date=@expiry_date
                             WHERE id=@id";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", item.Id);
        cmd.Parameters.AddWithValue("@item_name", item.ItemName);
        cmd.Parameters.AddWithValue("@quantity", item.Quantity);
        cmd.Parameters.AddWithValue("@unit", item.Unit);
        cmd.Parameters.AddWithValue("@expiry_date", item.ExpiryDate);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("DELETE FROM storage_items WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }
}
