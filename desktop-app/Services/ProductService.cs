using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class ProductService
{
    private const string SelectColumns = @"p.id, p.business_id, p.storage_item_id, p.nom, p.description, COALESCE(p.categorie,'') AS categorie, p.prix, p.quantite,
                                    p.date_expiration, p.type, p.image_url, p.is_active, b.nom_business";

    private static Produit MapProduit(MySqlDataReader reader)
    {
        return new Produit
        {
            Id = Convert.ToInt32(reader["id"]),
            BusinessId = Convert.ToInt32(reader["business_id"]),
            StorageItemId = reader["storage_item_id"] is DBNull ? null : Convert.ToInt32(reader["storage_item_id"]),
            Nom = Convert.ToString(reader["nom"]) ?? "",
            Description = Convert.ToString(reader["description"]) ?? "",
            Categorie = Convert.ToString(reader["categorie"]) ?? "",
            Prix = Convert.ToDecimal(reader["prix"]),
            Quantite = Convert.ToInt32(reader["quantite"]),
            DateExpiration = Convert.ToDateTime(reader["date_expiration"]),
            Type = Convert.ToString(reader["type"]) ?? "vente",
            ImageUrl = reader["image_url"] is DBNull ? null : Convert.ToString(reader["image_url"]),
            IsActive = Convert.ToInt32(reader["is_active"]) != 0,
            BusinessName = Convert.ToString(reader["nom_business"]) ?? ""
        };
    }

    public async Task<List<Produit>> GetAllAsync()
    {
        var list = new List<Produit>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = $@"SELECT {SelectColumns}
                             FROM produits p
                             JOIN business b ON b.id = p.business_id
                             ORDER BY p.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapProduit(reader));
        return list;
    }

    public async Task<List<Produit>> GetByBusinessAsync(int businessId)
    {
        var list = new List<Produit>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = $@"SELECT {SelectColumns}
                             FROM produits p
                             JOIN business b ON b.id = p.business_id
                             WHERE p.business_id = @businessId
                             ORDER BY p.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@businessId", businessId);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapProduit(reader));
        return list;
    }

    /// <summary>Active products in stock from validated businesses (client catalog).</summary>
    public async Task<List<Produit>> GetCatalogForClientAsync()
    {
        var list = new List<Produit>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        var sql = $@"SELECT {SelectColumns}
                             FROM produits p
                             JOIN business b ON b.id = p.business_id
                             WHERE p.is_active = 1 AND p.quantite > 0
                             AND LOWER(TRIM(COALESCE(b.status,''))) = 'valide'
                             ORDER BY p.date_expiration ASC, p.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = (MySqlDataReader)await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
            list.Add(MapProduit(reader));
        return list;
    }

    /// <summary>Creates a product listing and deducts <paramref name="listedQuantity"/> from the linked storage row (same transaction).</summary>
    public async Task<int> CreateFromStockAsync(Produit product, int storageItemId, int listedQuantity, string? imageUrl)
    {
        if (listedQuantity <= 0)
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
                var stockQty = Convert.ToDecimal(r["quantity"]);
                await r.CloseAsync();

                if (biz != product.BusinessId)
                    throw new Exception("Ce stock n'appartient pas à votre commerce.");
                if (stockQty < listedQuantity)
                    throw new Exception("Stock insuffisant pour cette quantité.");
            }

            const string deductSql = @"UPDATE storage_items
                                       SET quantity = quantity - @q
                                       WHERE id = @id AND business_id = @bid AND quantity >= @q";
            await using (var deductCmd = new MySqlCommand(deductSql, conn, tx))
            {
                deductCmd.Parameters.AddWithValue("@q", listedQuantity);
                deductCmd.Parameters.AddWithValue("@id", storageItemId);
                deductCmd.Parameters.AddWithValue("@bid", product.BusinessId);
                var rows = await deductCmd.ExecuteNonQueryAsync();
                if (rows <= 0)
                    throw new Exception("Impossible de déduire le stock (quantité insuffisante).");
            }

            var isActive = listedQuantity > 0 ? 1 : 0;
            const string insertSql = @"INSERT INTO produits(business_id, storage_item_id, nom, description, categorie, prix, quantite, date_expiration, type, image_url, is_active, created_at)
                                         VALUES(@business_id, @storage_item_id, @nom, @description, @categorie, @prix, @quantite, @date_expiration, @type, @image_url, @is_active, NOW());
                                         SELECT LAST_INSERT_ID();";
            await using var insertCmd = new MySqlCommand(insertSql, conn, tx);
            insertCmd.Parameters.AddWithValue("@business_id", product.BusinessId);
            insertCmd.Parameters.AddWithValue("@storage_item_id", storageItemId);
            insertCmd.Parameters.AddWithValue("@nom", product.Nom.Trim());
            insertCmd.Parameters.AddWithValue("@description", product.Description.Trim());
            insertCmd.Parameters.AddWithValue("@categorie", string.IsNullOrWhiteSpace(product.Categorie) ? (object)DBNull.Value : product.Categorie.Trim());
            insertCmd.Parameters.AddWithValue("@prix", product.Prix);
            insertCmd.Parameters.AddWithValue("@quantite", listedQuantity);
            insertCmd.Parameters.AddWithValue("@date_expiration", product.DateExpiration);
            insertCmd.Parameters.AddWithValue("@type", product.Type.Trim().ToLowerInvariant());
            insertCmd.Parameters.AddWithValue("@image_url", string.IsNullOrWhiteSpace(imageUrl) ? (object)DBNull.Value : imageUrl.Trim());
            insertCmd.Parameters.AddWithValue("@is_active", isActive);
            var idObj = await insertCmd.ExecuteScalarAsync();
            await tx.CommitAsync();
            return Convert.ToInt32(idObj);
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    private static async Task<int> CountPendingOrdersForProductAsync(MySqlConnection conn, MySqlTransaction tx, int productId)
    {
        const string sql = @"SELECT COUNT(*) FROM commandes_details cd
                             INNER JOIN commandes c ON c.id = cd.commande_id
                             WHERE cd.produit_id = @pid AND LOWER(TRIM(c.status)) = 'en_attente'";
        await using var cmd = new MySqlCommand(sql, conn, tx);
        cmd.Parameters.AddWithValue("@pid", productId);
        var scalar = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(scalar);
    }

    /// <summary>Soft delete: marks inactive and returns listed quantity to the originating storage row when possible.</summary>
    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            if (await CountPendingOrdersForProductAsync(conn, tx, id) > 0)
                throw new Exception("Des commandes en attente existent pour ce produit.");

            const string readSql = "SELECT storage_item_id, quantite, is_active FROM produits WHERE id=@id LIMIT 1";
            await using var readCmd = new MySqlCommand(readSql, conn, tx);
            readCmd.Parameters.AddWithValue("@id", id);
            await using var r = (MySqlDataReader)await readCmd.ExecuteReaderAsync();
            if (!await r.ReadAsync())
            {
                await r.CloseAsync();
                await tx.RollbackAsync();
                return false;
            }

            var storageId = r["storage_item_id"] is DBNull ? (int?)null : Convert.ToInt32(r["storage_item_id"]);
            var qty = Convert.ToInt32(r["quantite"]);
            var active = Convert.ToInt32(r["is_active"]) != 0;
            await r.CloseAsync();

            if (!active)
            {
                await tx.CommitAsync();
                return true;
            }

            const string deactivate = "UPDATE produits SET is_active=0 WHERE id=@id";
            await using (var u = new MySqlCommand(deactivate, conn, tx))
            {
                u.Parameters.AddWithValue("@id", id);
                await u.ExecuteNonQueryAsync();
            }

            if (storageId.HasValue && qty > 0)
            {
                const string restore = "UPDATE storage_items SET quantity = quantity + @q WHERE id=@sid";
                await using var rs = new MySqlCommand(restore, conn, tx);
                rs.Parameters.AddWithValue("@q", qty);
                rs.Parameters.AddWithValue("@sid", storageId.Value);
                await rs.ExecuteNonQueryAsync();
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
