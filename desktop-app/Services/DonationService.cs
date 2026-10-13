using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class DonationService
{
    private static Donation MapDonation(MySqlDataReader reader)
    {
        return new Donation
        {
            Id = Convert.ToInt32(reader["id"]),
            ProduitId = reader["produit_id"] is DBNull ? null : Convert.ToInt32(reader["produit_id"]),
            StorageItemId = reader["storage_item_id"] is DBNull ? null : Convert.ToInt32(reader["storage_item_id"]),
            AssociationId = Convert.ToInt32(reader["association_id"]),
            Quantite = Convert.ToInt32(reader["quantite"]),
            DateDonation = Convert.ToDateTime(reader["date_donation"]),
            Status = Convert.ToString(reader["status"]) ?? "Pending",
            ProduitNom = Convert.ToString(reader["article_nom"]) ?? "",
            AssociationNom = Convert.ToString(reader["association_nom"]) ?? "",
            BusinessNom = Convert.ToString(reader["nom_business"]) ?? ""
        };
    }

    private const string SelectBase = @"SELECT d.id, d.produit_id, d.storage_item_id, d.association_id, d.quantite, d.date_donation,
                                               COALESCE(d.status,'Pending') AS status,
                                               COALESCE(p.nom, si.item_name, '') AS article_nom,
                                               a.nom AS association_nom,
                                               b.nom_business
                                        FROM donations d
                                        JOIN associations a ON a.id = d.association_id
                                        LEFT JOIN produits p ON p.id = d.produit_id
                                        LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                        JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)";

    public async Task<List<Donation>> GetAllAsync()
    {
        var list = new List<Donation>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = SelectBase + " ORDER BY d.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapDonation(reader));
        return list;
    }

    public async Task<List<Donation>> GetByBusinessAsync(int businessId)
    {
        var list = new List<Donation>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = SelectBase + " WHERE b.id = @business_id ORDER BY d.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@business_id", businessId);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapDonation(reader));
        return list;
    }

    /// <summary>Association portal: only admin-approved donations, optionally filtered.</summary>
    public async Task<List<Donation>> GetDonationsForAssociationAsync(int associationId, string filter = "All")
    {
        var list = new List<Donation>();
        var f = (filter ?? "All").Trim();
        if (!string.Equals(f, "All", StringComparison.OrdinalIgnoreCase) &&
            !string.Equals(f, "Accepted", StringComparison.OrdinalIgnoreCase) &&
            !string.Equals(f, "Collected", StringComparison.OrdinalIgnoreCase))
            f = "All";

        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = SelectBase + @" WHERE a.id = @association_id
                                  AND d.status IN ('Accepted','Collected')
                                  AND (@filter = 'All' OR d.status = @filter)
                                  ORDER BY d.date_donation DESC, d.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@association_id", associationId);
        cmd.Parameters.AddWithValue("@filter", f);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapDonation(reader));
        return list;
    }

    public async Task<bool> MarkCollectedByAssociationAsync(int donationId, int associationId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"UPDATE donations
                             SET status='Collected', collected_at=NOW()
                             WHERE id=@id AND association_id=@aid AND status='Accepted'";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@id", donationId);
        cmd.Parameters.AddWithValue("@aid", associationId);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }

    /// <summary>Reserves quantity on <c>storage_items</c> and creates a pending donation.</summary>
    public async Task<int> CreateFromStorageAsync(int storageItemId, int associationId, int quantite, int expectedBusinessId)
    {
        if (quantite <= 0)
            throw new Exception("La quantité doit être supérieure à 0.");

        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string readSql = "SELECT business_id, quantity FROM storage_items WHERE id=@id LIMIT 1";
            await using (var readCmd = new MySqlCommand(readSql, conn, tx))
            {
                readCmd.Parameters.AddWithValue("@id", storageItemId);
                await using var r = (MySqlDataReader)await readCmd.ExecuteReaderAsync();
                if (!await r.ReadAsync())
                    throw new Exception("Ligne de stock introuvable.");
                var biz = Convert.ToInt32(r["business_id"]);
                var stock = Convert.ToDecimal(r["quantity"]);
                await r.CloseAsync();
                if (biz != expectedBusinessId)
                    throw new Exception("Ce stock n'appartient pas à votre commerce.");
                if (stock < quantite)
                    throw new Exception("Stock insuffisant pour ce don.");
            }

            const string insertSql = @"INSERT INTO donations(produit_id, storage_item_id, association_id, quantite, date_donation, status)
                                     VALUES (NULL, @sid, @aid, @q, NOW(), 'Pending')";
            await using var insertCmd = new MySqlCommand(insertSql, conn, tx);
            insertCmd.Parameters.AddWithValue("@sid", storageItemId);
            insertCmd.Parameters.AddWithValue("@aid", associationId);
            insertCmd.Parameters.AddWithValue("@q", quantite);
            await insertCmd.ExecuteNonQueryAsync();
            var newId = (int)insertCmd.LastInsertedId;

            const string deductSql = @"UPDATE storage_items
                                       SET quantity = quantity - @q
                                       WHERE id = @sid AND business_id = @bid AND quantity >= @q";
            await using var deductCmd = new MySqlCommand(deductSql, conn, tx);
            deductCmd.Parameters.AddWithValue("@q", quantite);
            deductCmd.Parameters.AddWithValue("@sid", storageItemId);
            deductCmd.Parameters.AddWithValue("@bid", expectedBusinessId);
            var rows = await deductCmd.ExecuteNonQueryAsync();
            if (rows <= 0)
                throw new Exception("Impossible de déduire le stock (quantité insuffisante).");

            await tx.CommitAsync();
            return newId;
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    private static async Task RestoreDonationInventoryAsync(MySqlConnection conn, MySqlTransaction tx, int? produitId, int? storageItemId, int qty)
    {
        if (qty <= 0)
            return;

        if (storageItemId.HasValue)
        {
            await using var up = new MySqlCommand("UPDATE storage_items SET quantity = quantity + @q WHERE id=@id", conn, tx);
            up.Parameters.AddWithValue("@q", qty);
            up.Parameters.AddWithValue("@id", storageItemId.Value);
            await up.ExecuteNonQueryAsync();
        }
        else if (produitId.HasValue)
        {
            await using var up = new MySqlCommand("UPDATE produits SET quantite = quantite + @q WHERE id=@id", conn, tx);
            up.Parameters.AddWithValue("@q", qty);
            up.Parameters.AddWithValue("@id", produitId.Value);
            await up.ExecuteNonQueryAsync();
        }
    }

    public async Task<bool> DeleteAsync(int donationId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string selectSql = "SELECT produit_id, storage_item_id, quantite FROM donations WHERE id=@id LIMIT 1";
            int? produitId = null;
            int? storageItemId = null;
            var qty = 0;
            await using (var selectCmd = new MySqlCommand(selectSql, conn, tx))
            {
                selectCmd.Parameters.AddWithValue("@id", donationId);
                await using var reader = (MySqlDataReader)await selectCmd.ExecuteReaderAsync();
                if (!await reader.ReadAsync())
                {
                    await tx.RollbackAsync();
                    return false;
                }

                produitId = reader["produit_id"] is DBNull ? null : Convert.ToInt32(reader["produit_id"]);
                storageItemId = reader["storage_item_id"] is DBNull ? null : Convert.ToInt32(reader["storage_item_id"]);
                qty = Convert.ToInt32(reader["quantite"]);
                await reader.CloseAsync();
            }

            await using var delCmd = new MySqlCommand("DELETE FROM donations WHERE id=@id", conn, tx);
            delCmd.Parameters.AddWithValue("@id", donationId);
            var deleted = await delCmd.ExecuteNonQueryAsync() > 0;
            if (!deleted)
            {
                await tx.RollbackAsync();
                return false;
            }

            await RestoreDonationInventoryAsync(conn, tx, produitId, storageItemId, qty);
            await tx.CommitAsync();
            return true;
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    public async Task<bool> UpdateStatusAsync(int donationId, string status)
    {
        var newStatus = (status ?? "").Trim();
        if (newStatus.Length == 0)
            return false;

        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            string oldStatus;
            int? produitId = null;
            int? storageItemId = null;
            var qty = 0;
            const string sel = "SELECT status, produit_id, storage_item_id, quantite FROM donations WHERE id=@id LIMIT 1";
            await using (var selCmd = new MySqlCommand(sel, conn, tx))
            {
                selCmd.Parameters.AddWithValue("@id", donationId);
                await using var r = (MySqlDataReader)await selCmd.ExecuteReaderAsync();
                if (!await r.ReadAsync())
                {
                    await tx.RollbackAsync();
                    return false;
                }

                oldStatus = Convert.ToString(r["status"]) ?? "Pending";
                produitId = r["produit_id"] is DBNull ? null : Convert.ToInt32(r["produit_id"]);
                storageItemId = r["storage_item_id"] is DBNull ? null : Convert.ToInt32(r["storage_item_id"]);
                qty = Convert.ToInt32(r["quantite"]);
                await r.CloseAsync();
            }

            if (string.Equals(newStatus, "Rejected", StringComparison.OrdinalIgnoreCase) &&
                string.Equals(oldStatus, "Pending", StringComparison.OrdinalIgnoreCase))
                await RestoreDonationInventoryAsync(conn, tx, produitId, storageItemId, qty);

            var sql = string.Equals(newStatus, "Collected", StringComparison.OrdinalIgnoreCase)
                ? "UPDATE donations SET status=@status, collected_at=NOW() WHERE id=@id"
                : "UPDATE donations SET status=@status WHERE id=@id";
            await using var cmd = new MySqlCommand(sql, conn, tx);
            cmd.Parameters.AddWithValue("@status", newStatus);
            cmd.Parameters.AddWithValue("@id", donationId);
            var n = await cmd.ExecuteNonQueryAsync();
            if (n == 0)
            {
                await tx.RollbackAsync();
                return false;
            }

            await tx.CommitAsync();
            return true;
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }
}
