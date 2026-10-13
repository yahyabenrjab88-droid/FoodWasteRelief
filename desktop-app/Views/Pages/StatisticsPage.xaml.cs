using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Globalization;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using System.Windows.Media;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Pages;

public partial class StatisticsPage : Page, INotifyPropertyChanged
{
    private readonly StatisticsService _stats = new();

    public event PropertyChangedEventHandler? PropertyChanged;
    private void OnPropertyChanged([CallerMemberName] string? name = null) =>
        PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));

    private bool _impactLoading;
    public bool ImpactLoading { get => _impactLoading; set { _impactLoading = value; OnPropertyChanged(); } }

    private bool _impactError;
    public bool ImpactError { get => _impactError; set { _impactError = value; OnPropertyChanged(); } }

    private string _impactErrorText = "Erreur de chargement des statistiques d'impact. Veuillez réessayer.";
    public string ImpactErrorText { get => _impactErrorText; set { _impactErrorText = value; OnPropertyChanged(); } }

    private bool _impactEmpty;
    public bool ImpactEmpty { get => _impactEmpty; set { _impactEmpty = value; OnPropertyChanged(); } }

    private decimal _totalKg;
    public decimal TotalKg { get => _totalKg; set { _totalKg = value; OnPropertyChanged(); OnPropertyChanged(nameof(TickerText)); OnPropertyChanged(nameof(TickerSubText)); } }

    private int _meals;
    public int Meals { get => _meals; set { _meals = value; OnPropertyChanged(); OnPropertyChanged(nameof(TickerText)); } }

    private decimal _co2;
    public decimal Co2 { get => _co2; set { _co2 = value; OnPropertyChanged(); OnPropertyChanged(nameof(TickerText)); } }

    private int _donationsAcceptedCount;
    public int DonationsAcceptedCount { get => _donationsAcceptedCount; set { _donationsAcceptedCount = value; OnPropertyChanged(); } }

    private int _ordersCompletedCount;
    public int OrdersCompletedCount { get => _ordersCompletedCount; set { _ordersCompletedCount = value; OnPropertyChanged(); } }

    private int _activeBusinesses;
    public int ActiveBusinesses { get => _activeBusinesses; set { _activeBusinesses = value; OnPropertyChanged(); } }

    private decimal _totalDonatedKg;
    public decimal TotalDonatedKg { get => _totalDonatedKg; set { _totalDonatedKg = value; OnPropertyChanged(); } }

    private decimal _totalSoldKg;
    public decimal TotalSoldKg { get => _totalSoldKg; set { _totalSoldKg = value; OnPropertyChanged(); } }

    public string TickerText =>
        $"🌍  {TotalKg:0.#} kg de nourriture sauvés du gaspillage sur notre plateforme — soit {Meals} repas et {Co2:0.#} kg de CO2 évités";
    public string TickerSubText => "Données mises à jour à chaque chargement de la page.";

    public ObservableCollection<(string Label, decimal DonKg, decimal SoldKg, decimal TotalKg)> Last6Months { get; } = new();
    public ObservableCollection<(string Name, decimal Kg)> TopBusinesses { get; } = new();
    public ObservableCollection<(string Name, decimal Kg)> TopAssociations { get; } = new();

    public ObservableCollection<BusinessImpactRow> BusinessRows { get; } = new();

    public StatisticsPage()
    {
        InitializeComponent();
        DataContext = this;
        Loaded += StatisticsPage_Loaded;
    }

    private async void StatisticsPage_Loaded(object sender, RoutedEventArgs e)
    {
        await LoadAllAsync();
    }

    private async Task LoadAllAsync()
    {
        try
        {
            Mouse.OverrideCursor = Cursors.Wait;
            await LoadPlatformKpisAsync();
            await LoadImpactAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show($"Stats error: {ex.Message}", "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            NoDataText.Visibility = Visibility.Visible;
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async Task LoadPlatformKpisAsync()
    {
        var users = await _stats.GetUsersCountAsync();
        var products = await _stats.GetProductsCountAsync();
        var meals = await _stats.GetTotalDonatedMealsAsync();
        var revenue = await _stats.GetDeliveredRevenueAsync();

        UsersCountText.Text = users.ToString();
        ProductsCountText.Text = products.ToString();
        DonationsCountText.Text = meals.ToString();
        RevenueText.Text = $"{revenue:F2} TND";
        NoDataText.Visibility = (users + products + meals) == 0 ? Visibility.Visible : Visibility.Collapsed;
    }

    private static DateTime StartOfMonth(DateTime dt) => new(dt.Year, dt.Month, 1);
    private static string MonthLabel(DateTime dt) => dt.ToString("MMM", CultureInfo.GetCultureInfo("fr-FR"));

    private static readonly string[] SalesStatuses = { "livree", "confirmee", "en_cours_de_livraison" };

    private async Task LoadImpactAsync()
    {
        ImpactLoading = true;
        ImpactError = false;
        ImpactEmpty = false;
        try
        {
            var tasks = new List<Task>
            {
                LoadImpactTotalsAsync(),
                LoadImpactLast6MonthsAsync(),
                LoadImpactLeaderboardsAsync(),
                LoadImpactBusinessTableAsync()
            };

            await Task.WhenAll(tasks);

            ImpactEmpty = TotalKg <= 0.0001m;
            DrawCharts();
        }
        catch (Exception ex)
        {
            ImpactError = true;
            ImpactErrorText = "Erreur de chargement des statistiques d'impact. Veuillez réessayer.\n" + ex.Message;
        }
        finally
        {
            ImpactLoading = false;
        }
    }

    private async Task LoadImpactTotalsAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();

        // Total donated kg across all donations accepted/collected.
        const string donatedSql = @"SELECT COALESCE(SUM(quantite),0)
                                    FROM donations
                                    WHERE status IN ('Accepted','Collected')";
        await using (var cmd = new MySqlCommand(donatedSql, conn))
            TotalDonatedKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());

        // Total sold qty across all order line items for delivered/confirmed/in-progress delivery.
        var soldSql = @"SELECT COALESCE(SUM(cd.quantite),0)
                        FROM commandes_details cd
                        JOIN commandes c ON c.id = cd.commande_id
                        WHERE c.status IN ('livree','confirmee','en_cours_de_livraison')";
        await using (var cmd = new MySqlCommand(soldSql, conn))
            TotalSoldKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());

        TotalKg = TotalDonatedKg + TotalSoldKg;
        Meals = (int)Math.Round((double)(TotalKg * 2m), MidpointRounding.AwayFromZero);
        Co2 = Math.Round(TotalKg * 2.5m, 1);

        const string donationsCountSql = @"SELECT COUNT(*)
                                           FROM donations
                                           WHERE status IN ('Accepted','Collected')";
        await using (var cmd = new MySqlCommand(donationsCountSql, conn))
            DonationsAcceptedCount = Convert.ToInt32(await cmd.ExecuteScalarAsync());

        const string ordersCountSql = @"SELECT COUNT(*)
                                        FROM commandes
                                        WHERE status IN ('livree','confirmee','en_cours_de_livraison')";
        await using (var cmd = new MySqlCommand(ordersCountSql, conn))
            OrdersCompletedCount = Convert.ToInt32(await cmd.ExecuteScalarAsync());

        const string activeBizSql = @"SELECT COUNT(*)
                                      FROM business
                                      WHERE status='valide' AND is_active=1";
        await using (var cmd = new MySqlCommand(activeBizSql, conn))
            ActiveBusinesses = Convert.ToInt32(await cmd.ExecuteScalarAsync());
    }

    private async Task LoadImpactLast6MonthsAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();

        Last6Months.Clear();
        var months = new List<DateTime>();
        for (var i = 5; i >= 0; i--)
            months.Add(StartOfMonth(DateTime.Now).AddMonths(-i));
        var from = months[0];

        var donatedMap = new Dictionary<(int Y, int M), decimal>();
        const string donated6Sql = @"SELECT YEAR(date_donation) AS y, MONTH(date_donation) AS m, COALESCE(SUM(quantite),0) AS total
                                     FROM donations
                                     WHERE status IN ('Accepted','Collected') AND date_donation >= @from
                                     GROUP BY YEAR(date_donation), MONTH(date_donation)
                                     ORDER BY y, m";
        await using (var cmd = new MySqlCommand(donated6Sql, conn))
        {
            cmd.Parameters.AddWithValue("@from", from);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                donatedMap[(Convert.ToInt32(r["y"]), Convert.ToInt32(r["m"]))] = Convert.ToDecimal(r["total"]);
        }

        var soldMap = new Dictionary<(int Y, int M), decimal>();
        const string sold6Sql = @"SELECT YEAR(c.date_commande) AS y, MONTH(c.date_commande) AS m, COALESCE(SUM(cd.quantite),0) AS total
                                  FROM commandes_details cd
                                  JOIN commandes c ON c.id = cd.commande_id
                                  WHERE c.status IN ('livree','confirmee','en_cours_de_livraison') AND c.date_commande >= @from
                                  GROUP BY YEAR(c.date_commande), MONTH(c.date_commande)
                                  ORDER BY y, m";
        await using (var cmd = new MySqlCommand(sold6Sql, conn))
        {
            cmd.Parameters.AddWithValue("@from", from);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                soldMap[(Convert.ToInt32(r["y"]), Convert.ToInt32(r["m"]))] = Convert.ToDecimal(r["total"]);
        }

        foreach (var dt in months)
        {
            donatedMap.TryGetValue((dt.Year, dt.Month), out var dkg);
            soldMap.TryGetValue((dt.Year, dt.Month), out var skg);
            var tkg = dkg + skg;
            Last6Months.Add((MonthLabel(dt), dkg, skg, tkg));
        }
    }

    private async Task LoadImpactLeaderboardsAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();

        TopBusinesses.Clear();
        TopAssociations.Clear();

        // Top 5 associations by collected kg.
        const string topAssocSql = @"SELECT a.nom AS name, COALESCE(SUM(d.quantite),0) AS collected
                                     FROM associations a
                                     LEFT JOIN donations d ON d.association_id = a.id AND d.status='Collected'
                                     GROUP BY a.id, a.nom
                                     ORDER BY collected DESC
                                     LIMIT 5";
        await using (var cmd = new MySqlCommand(topAssocSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                TopAssociations.Add((Convert.ToString(r["name"]) ?? "", Convert.ToDecimal(r["collected"])));
        }

        // Top 5 businesses by donated+sold.
        const string bizBase = @"SELECT b.id, b.nom_business
                                 FROM business b";
        var bizNames = new Dictionary<int, string>();
        await using (var cmd = new MySqlCommand(bizBase, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                bizNames[Convert.ToInt32(r["id"])] = Convert.ToString(r["nom_business"]) ?? "";
        }

        var donated = new Dictionary<int, decimal>();
        const string donatedByBizSql = @"SELECT b.id AS bid, COALESCE(SUM(d.quantite),0) AS donated
                                         FROM business b
                                         LEFT JOIN produits p ON p.business_id = b.id
                                         LEFT JOIN donations d ON d.produit_id = p.id AND d.status IN ('Accepted','Collected')
                                         GROUP BY b.id";
        await using (var cmd = new MySqlCommand(donatedByBizSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                donated[Convert.ToInt32(r["bid"])] = Convert.ToDecimal(r["donated"]);
        }

        var sold = new Dictionary<int, decimal>();
        const string soldByBizSql = @"SELECT p.business_id AS bid, COALESCE(SUM(cd.quantite),0) AS sold
                                      FROM commandes_details cd
                                      JOIN commandes c ON c.id = cd.commande_id
                                      JOIN produits p ON p.id = cd.produit_id
                                      WHERE c.status IN ('livree','confirmee','en_cours_de_livraison')
                                      GROUP BY p.business_id";
        await using (var cmd = new MySqlCommand(soldByBizSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                sold[Convert.ToInt32(r["bid"])] = Convert.ToDecimal(r["sold"]);
        }

        var totals = new List<(int Id, string Name, decimal Kg)>();
        foreach (var (id, name) in bizNames)
        {
            donated.TryGetValue(id, out var dkg);
            sold.TryGetValue(id, out var skg);
            totals.Add((id, name, dkg + skg));
        }

        foreach (var row in totals.OrderByDescending(x => x.Kg).Take(5))
            TopBusinesses.Add((row.Name, row.Kg));
    }

    private async Task LoadImpactBusinessTableAsync()
    {
        await using var conn = await DatabaseService.GetConnectionAsync();

        BusinessRows.Clear();

        // Per-business donated kg (donations tied to produits, accepted/collected).
        var donated = new Dictionary<int, decimal>();
        const string donatedByBizSql = @"SELECT b.id AS bid, COALESCE(SUM(d.quantite),0) AS donated,
                                                COUNT(d.id) AS donCount
                                         FROM business b
                                         LEFT JOIN produits p ON p.business_id = b.id
                                         LEFT JOIN donations d ON d.produit_id = p.id AND d.status IN ('Accepted','Collected')
                                         GROUP BY b.id";
        var donatedCount = new Dictionary<int, int>();
        await using (var cmd = new MySqlCommand(donatedByBizSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var id = Convert.ToInt32(r["bid"]);
                donated[id] = Convert.ToDecimal(r["donated"]);
                donatedCount[id] = Convert.ToInt32(r["donCount"]);
            }
        }

        // Per-business sold kg and orders count.
        var sold = new Dictionary<int, decimal>();
        var ordersCount = new Dictionary<int, int>();
        const string soldByBizSql = @"SELECT p.business_id AS bid,
                                             COALESCE(SUM(cd.quantite),0) AS sold,
                                             COUNT(DISTINCT c.id) AS ordersCount
                                      FROM commandes_details cd
                                      JOIN commandes c ON c.id = cd.commande_id
                                      JOIN produits p ON p.id = cd.produit_id
                                      WHERE c.status IN ('livree','confirmee','en_cours_de_livraison')
                                      GROUP BY p.business_id";
        await using (var cmd = new MySqlCommand(soldByBizSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var id = Convert.ToInt32(r["bid"]);
                sold[id] = Convert.ToDecimal(r["sold"]);
                ordersCount[id] = Convert.ToInt32(r["ordersCount"]);
            }
        }

        // Business names.
        var names = new Dictionary<int, string>();
        const string namesSql = @"SELECT id, nom_business FROM business";
        await using (var cmd = new MySqlCommand(namesSql, conn))
        {
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                names[Convert.ToInt32(r["id"])] = Convert.ToString(r["nom_business"]) ?? "";
        }

        var rows = new List<BusinessImpactRow>();
        foreach (var (id, name) in names)
        {
            donated.TryGetValue(id, out var dkg);
            sold.TryGetValue(id, out var skg);
            donatedCount.TryGetValue(id, out var dons);
            ordersCount.TryGetValue(id, out var ord);
            var total = dkg + skg;
            rows.Add(new BusinessImpactRow
            {
                BusinessName = name,
                KgDonated = dkg,
                KgSold = skg,
                TotalKg = total,
                Meals = (int)Math.Round((double)(total * 2m), MidpointRounding.AwayFromZero),
                Co2 = Math.Round(total * 2.5m, 1),
                DonationsCount = dons,
                OrdersCount = ord
            });
        }

        rows = rows.OrderByDescending(r => r.TotalKg).ToList();
        if (rows.Count > 0)
            rows[0].IsTopPerformer = true;

        foreach (var r in rows)
            BusinessRows.Add(r);

        // Totals row.
        if (rows.Count > 0)
        {
            var tot = new BusinessImpactRow
            {
                BusinessName = "TOTAL",
                KgDonated = rows.Sum(x => x.KgDonated),
                KgSold = rows.Sum(x => x.KgSold),
                TotalKg = rows.Sum(x => x.TotalKg),
                Meals = rows.Sum(x => x.Meals),
                Co2 = Math.Round(rows.Sum(x => x.TotalKg) * 2.5m, 1),
                DonationsCount = rows.Sum(x => x.DonationsCount),
                OrdersCount = rows.Sum(x => x.OrdersCount),
                IsTotals = true
            };
            BusinessRows.Add(tot);
        }
    }

    private void DrawCharts()
    {
        if (FindName("ImpactMonthlyCanvas") is Canvas monthly)
            DrawMonthlyChart(monthly, Last6Months);
        if (FindName("ImpactSplitCanvas") is Canvas split)
            DrawSplitBar(split, TotalDonatedKg, TotalSoldKg, ColorFrom("#2D6A4F"), ColorFrom("#52B788"));
        if (FindName("TopBizCanvas") is Canvas topBiz)
            DrawHorizontalBars(topBiz, TopBusinesses, ColorFrom("#52B788"), showStarForFirst: true);
        if (FindName("TopAssocCanvas") is Canvas topAssoc)
            DrawHorizontalBars(topAssoc, TopAssociations, ColorFrom("#52B788"), showStarForFirst: false);
    }

    private static Color ColorFrom(string hex) => (Color)ColorConverter.ConvertFromString(hex)!;

    private static void DrawSplitBar(Canvas canvas, decimal donatedKg, decimal soldKg, Color donatedColor, Color soldColor)
    {
        canvas.Children.Clear();
        var w = Math.Max(260, canvas.ActualWidth);
        var h = Math.Max(50, canvas.ActualHeight);
        var total = donatedKg + soldKg;
        if (total <= 0.0001m) total = 1m;

        var pad = 10;
        var barH = 16;
        var barY = (h - barH) / 2;
        var barW = w - pad * 2;
        var wDon = barW * (double)(donatedKg / total);
        var wSold = Math.Max(0, barW - wDon);

        var r1 = new System.Windows.Shapes.Rectangle { Width = wDon, Height = barH, Fill = new SolidColorBrush(donatedColor), RadiusX = 4, RadiusY = 4 };
        Canvas.SetLeft(r1, pad);
        Canvas.SetTop(r1, barY);
        canvas.Children.Add(r1);

        var r2 = new System.Windows.Shapes.Rectangle { Width = wSold, Height = barH, Fill = new SolidColorBrush(soldColor), RadiusX = 4, RadiusY = 4 };
        Canvas.SetLeft(r2, pad + wDon);
        Canvas.SetTop(r2, barY);
        canvas.Children.Add(r2);
    }

    private static void DrawHorizontalBars(Canvas canvas, IEnumerable<(string Name, decimal Kg)> points, Color barColor, bool showStarForFirst)
    {
        canvas.Children.Clear();
        var list = points.ToList();
        if (list.Count == 0) return;

        var w = Math.Max(420, canvas.ActualWidth);
        var rowH = 28;
        canvas.Height = Math.Max(rowH * list.Count + 10, canvas.ActualHeight);

        var max = Math.Max(0.1m, list.Max(x => x.Kg));
        var leftLabelW = 220;
        var rightW = Math.Max(120, w - leftLabelW - 80);

        for (var i = 0; i < list.Count; i++)
        {
            var (name, kg) = list[i];
            var y = 8 + i * rowH;

            var prefix = (showStarForFirst && i == 0) ? "★ " : "";
            var lab = new TextBlock { Text = prefix + name, FontSize = 12, Foreground = new SolidColorBrush(ColorFrom("#1B2D23")) };
            Canvas.SetLeft(lab, 0);
            Canvas.SetTop(lab, y);
            canvas.Children.Add(lab);

            var bw = rightW * (double)(kg / max);
            var rect = new System.Windows.Shapes.Rectangle { Width = Math.Max(2, bw), Height = 10, Fill = new SolidColorBrush(barColor), RadiusX = 3, RadiusY = 3 };
            Canvas.SetLeft(rect, leftLabelW);
            Canvas.SetTop(rect, y + 6);
            canvas.Children.Add(rect);

            var val = new TextBlock { Text = $"{kg:0.#} kg", FontSize = 11, Foreground = new SolidColorBrush(ColorFrom("#6B7C72")) };
            Canvas.SetLeft(val, leftLabelW + rightW + 6);
            Canvas.SetTop(val, y + 2);
            canvas.Children.Add(val);
        }
    }

    private static void DrawMonthlyChart(Canvas canvas, IEnumerable<(string Label, decimal DonKg, decimal SoldKg, decimal TotalKg)> points)
    {
        canvas.Children.Clear();
        var list = points.ToList();
        if (list.Count == 0) return;

        var w = Math.Max(520, canvas.ActualWidth);
        var h = Math.Max(220, canvas.ActualHeight);
        var padL = 28;
        var padR = 10;
        var padT = 18;
        var padB = 26;
        var plotW = w - padL - padR;
        var plotH = h - padT - padB;

        var max = Math.Max(0.1m, list.Max(x => x.TotalKg));
        var stepX = plotW / Math.Max(1, list.Count - 1);

        // Bars: donations + sales stacked.
        for (var i = 0; i < list.Count; i++)
        {
            var x = padL + i * stepX;
            var barW = Math.Max(14, stepX * 0.45);
            var donH = (double)(list[i].DonKg / max) * plotH;
            var soldH = (double)(list[i].SoldKg / max) * plotH;
            var baseY = padT + plotH;

            var rDon = new System.Windows.Shapes.Rectangle { Width = barW, Height = Math.Max(1, donH), Fill = new SolidColorBrush(ColorFrom("#2D6A4F")) };
            Canvas.SetLeft(rDon, x - barW / 2);
            Canvas.SetTop(rDon, baseY - donH);
            canvas.Children.Add(rDon);

            var rSold = new System.Windows.Shapes.Rectangle { Width = barW, Height = Math.Max(1, soldH), Fill = new SolidColorBrush(ColorFrom("#52B788")) };
            Canvas.SetLeft(rSold, x - barW / 2);
            Canvas.SetTop(rSold, baseY - donH - soldH);
            canvas.Children.Add(rSold);

            var lab = new TextBlock { Text = list[i].Label, FontSize = 11, Foreground = new SolidColorBrush(ColorFrom("#6B7C72")) };
            Canvas.SetLeft(lab, x - 10);
            Canvas.SetTop(lab, padT + plotH + 6);
            canvas.Children.Add(lab);
        }

        // Total line (dashed black).
        var poly = new System.Windows.Shapes.Polyline
        {
            Stroke = new SolidColorBrush(Colors.Black),
            StrokeThickness = 2,
            StrokeDashArray = new DoubleCollection { 4, 3 }
        };
        for (var i = 0; i < list.Count; i++)
        {
            var x = padL + i * stepX;
            var y = padT + plotH - ((double)(list[i].TotalKg / max) * plotH);
            poly.Points.Add(new System.Windows.Point(x, y));
        }
        canvas.Children.Add(poly);
    }

    private void RetryImpact_Click(object sender, RoutedEventArgs e) => _ = LoadImpactAsync();

    public sealed class BusinessImpactRow : INotifyPropertyChanged
    {
        public event PropertyChangedEventHandler? PropertyChanged;
        private void OnPropertyChanged([CallerMemberName] string? name = null) =>
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));

        public string BusinessName { get; set; } = "";
        public decimal KgDonated { get; set; }
        public decimal KgSold { get; set; }
        public decimal TotalKg { get; set; }
        public int Meals { get; set; }
        public decimal Co2 { get; set; }
        public int DonationsCount { get; set; }
        public int OrdersCount { get; set; }

        private bool _isTopPerformer;
        public bool IsTopPerformer { get => _isTopPerformer; set { _isTopPerformer = value; OnPropertyChanged(); } }

        private bool _isTotals;
        public bool IsTotals { get => _isTotals; set { _isTotals = value; OnPropertyChanged(); } }
    }
}

