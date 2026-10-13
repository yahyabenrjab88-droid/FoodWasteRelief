using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class CartService
{
    public async Task<List<CartLine>> GetLinesAsync(int clientId)
    {
        var list = new List<CartLine>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT ci.id AS cart_id, ci.product_id, ci.quantity,
                                    p.nom AS produit_nom, p.prix, p.quantite AS stock_dispo, b.nom_business
                             FROM cart_items ci
                             JOIN produits p ON p.id = ci.product_id
                             JOIN business b ON b.id = p.business_id
                             WHERE ci.client_id = @cid AND p.is_active = 1
                             ORDER BY ci.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@cid", clientId);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new CartLine
            {
                CartItemId = Convert.ToInt32(reader["cart_id"]),
                ProduitId = Convert.ToInt32(reader["product_id"]),
                Quantite = Convert.ToInt32(reader["quantity"]),
                ProduitNom = Convert.ToString(reader["produit_nom"]) ?? "",
                PrixUnitaire = Convert.ToDecimal(reader["prix"]),
                StockDisponible = Convert.ToInt32(reader["stock_dispo"]),
                BusinessName = Convert.ToString(reader["nom_business"]) ?? ""
            });
        }

        return list;
    }

    public async Task AddOrUpdateQuantityAsync(int clientId, int productId, int deltaQty)
    {
        if (deltaQty == 0)
            return;

        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string stockSql = "SELECT quantite FROM produits WHERE id=@pid AND is_active=1 LIMIT 1";
            await using (var sc = new MySqlCommand(stockSql, conn, tx))
            {
                sc.Parameters.AddWithValue("@pid", productId);
                var obj = await sc.ExecuteScalarAsync();
                if (obj is null || obj is DBNull)
                    throw new Exception("Produit introuvable ou indisponible.");
                var stock = Convert.ToInt32(obj);

                const string curSql = "SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE client_id=@cid AND product_id=@pid";
                await using var cc = new MySqlCommand(curSql, conn, tx);
                cc.Parameters.AddWithValue("@cid", clientId);
                cc.Parameters.AddWithValue("@pid", productId);
                var curObj = await cc.ExecuteScalarAsync();
                var current = Convert.ToInt32(curObj);
                var newQty = current + deltaQty;
                if (newQty < 0)
                    newQty = 0;
                if (newQty > stock)
                    throw new Exception("Quantité supérieure au stock disponible.");

                if (newQty == 0)
                {
                    const string del = "DELETE FROM cart_items WHERE client_id=@cid AND product_id=@pid";
                    await using var delCmd = new MySqlCommand(del, conn, tx);
                    delCmd.Parameters.AddWithValue("@cid", clientId);
                    delCmd.Parameters.AddWithValue("@pid", productId);
                    await delCmd.ExecuteNonQueryAsync();
                }
                else
                {
                    const string upsert = @"INSERT INTO cart_items(client_id, product_id, quantity, added_at)
                                            VALUES(@cid, @pid, @qty, NOW())
                                            ON DUPLICATE KEY UPDATE quantity = @qty";
                    await using var up = new MySqlCommand(upsert, conn, tx);
                    up.Parameters.AddWithValue("@cid", clientId);
                    up.Parameters.AddWithValue("@pid", productId);
                    up.Parameters.AddWithValue("@qty", newQty);
                    await up.ExecuteNonQueryAsync();
                }
            }

            await tx.CommitAsync();
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    public async Task SetLineQuantityAsync(int clientId, int cartItemId, int newQty)
    {
        if (newQty < 0)
            newQty = 0;

        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string readSql = @"SELECT ci.product_id, p.quantite AS stock
                                     FROM cart_items ci
                                     JOIN produits p ON p.id = ci.product_id
                                     WHERE ci.id=@cart AND ci.client_id=@cid LIMIT 1";
            await using var readCmd = new MySqlCommand(readSql, conn, tx);
            readCmd.Parameters.AddWithValue("@cart", cartItemId);
            readCmd.Parameters.AddWithValue("@cid", clientId);
            await using var r = await readCmd.ExecuteReaderAsync();
            if (!await r.ReadAsync())
            {
                await r.CloseAsync();
                await tx.RollbackAsync();
                return;
            }

            var productId = Convert.ToInt32(r["product_id"]);
            var stock = Convert.ToInt32(r["stock"]);
            await r.CloseAsync();

            if (newQty > stock)
                throw new Exception("Quantité supérieure au stock disponible.");

            if (newQty == 0)
            {
                await using var del = new MySqlCommand("DELETE FROM cart_items WHERE id=@id AND client_id=@cid", conn, tx);
                del.Parameters.AddWithValue("@id", cartItemId);
                del.Parameters.AddWithValue("@cid", clientId);
                await del.ExecuteNonQueryAsync();
            }
            else
            {
                await using var up = new MySqlCommand("UPDATE cart_items SET quantity=@q WHERE id=@id AND client_id=@cid", conn, tx);
                up.Parameters.AddWithValue("@q", newQty);
                up.Parameters.AddWithValue("@id", cartItemId);
                up.Parameters.AddWithValue("@cid", clientId);
                await up.ExecuteNonQueryAsync();
            }

            await tx.CommitAsync();
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    public async Task RemoveLineAsync(int clientId, int cartItemId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("DELETE FROM cart_items WHERE id=@id AND client_id=@cid", conn);
        cmd.Parameters.AddWithValue("@id", cartItemId);
        cmd.Parameters.AddWithValue("@cid", clientId);
        await cmd.ExecuteNonQueryAsync();
    }
}
