using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Globalization;
using System.Runtime.CompilerServices;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Media;
using System.Windows.Media.Animation;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Pages;

public partial class DashboardPage : Page, INotifyPropertyChanged
{
    private readonly StatisticsService _stats = new();
    private readonly OrderService _orders = new();
    private readonly DonationService _donations = new();

    public bool IsBusinessView => Session.IsBusiness;
    public bool IsAssociationView => Session.IsAssociation;
    public bool AssocHasReceived => AssocReceivedKg > 0.0001m;

    public string Subtitle => Session.IsAdmin
        ? "Vue globale de la plateforme"
        : Session.IsBusiness
            ? "Vue etablissement"
            : Session.IsAssociation
                ? "Vue association"
            : "Vue client";
    public string WelcomeText => $"Bonjour, {Session.CurrentUser?.Prenom ?? "Utilisateur"}";

    private int _totalUsers;
    public int TotalUsers { get => _totalUsers; set { _totalUsers = value; OnPropertyChanged(); } }

    private int _pendingApprovals;
    public int PendingApprovals { get => _pendingApprovals; set { _pendingApprovals = value; OnPropertyChanged(); } }

    private int _productsCount;
    public int ProductsCount { get => _productsCount; set { _productsCount = value; OnPropertyChanged(); } }

    private int _totalMealsDonated;
    public int TotalMealsDonated { get => _totalMealsDonated; set { _totalMealsDonated = value; OnPropertyChanged(); } }
    public ObservableCollection<string> RecentActivity { get; } = new();

    private bool _impactLoading;
    public bool ImpactLoading { get => _impactLoading; set { _impactLoading = value; OnPropertyChanged(); } }

    private bool _impactError;
    public bool ImpactError { get => _impactError; set { _impactError = value; OnPropertyChanged(); } }

    private string _impactErrorText = "Impossible de charger les statistiques.";
    public string ImpactErrorText { get => _impactErrorText; set { _impactErrorText = value; OnPropertyChanged(); } }

    private bool _impactEmpty;
    public bool ImpactEmpty { get => _impactEmpty; set { _impactEmpty = value; OnPropertyChanged(); } }

    private decimal _impactTotalKg;
    public decimal ImpactTotalKg { get => _impactTotalKg; set { _impactTotalKg = value; OnPropertyChanged(); } }

    private int _impactMeals;
    public int ImpactMeals { get => _impactMeals; set { _impactMeals = value; OnPropertyChanged(); } }

    private decimal _impactCo2;
    public decimal ImpactCo2 { get => _impactCo2; set { _impactCo2 = value; OnPropertyChanged(); } }

    private int _impactDonationsCount;
    public int ImpactDonationsCount { get => _impactDonationsCount; set { _impactDonationsCount = value; OnPropertyChanged(); } }

    private decimal _impactDonatedKg;
    public decimal ImpactDonatedKg { get => _impactDonatedKg; set { _impactDonatedKg = value; OnPropertyChanged(); } }

    private decimal _impactSoldKg;
    public decimal ImpactSoldKg { get => _impactSoldKg; set { _impactSoldKg = value; OnPropertyChanged(); } }

    private decimal _impactMonthKg;
    public decimal ImpactMonthKg { get => _impactMonthKg; set { _impactMonthKg = value; OnPropertyChanged(); } }

    private bool _impactGoalHit;
    public bool ImpactGoalHit { get => _impactGoalHit; set { _impactGoalHit = value; OnPropertyChanged(); } }

    private bool _impactIsEstimation;
    public bool ImpactIsEstimation { get => _impactIsEstimation; set { _impactIsEstimation = value; OnPropertyChanged(); } }

    private decimal _assocReceivedKg;
    public decimal AssocReceivedKg { get => _assocReceivedKg; set { _assocReceivedKg = value; OnPropertyChanged(); } }

    private decimal _assocCollectedKg;
    public decimal AssocCollectedKg { get => _assocCollectedKg; set { _assocCollectedKg = value; OnPropertyChanged(); } }

    private decimal _assocCo2;
    public decimal AssocCo2 { get => _assocCo2; set { _assocCo2 = value; OnPropertyChanged(); } }

    private int _assocMeals;
    public int AssocMeals { get => _assocMeals; set { _assocMeals = value; OnPropertyChanged(); } }

    private int _assocCollectRate;
    public int AssocCollectRate { get => _assocCollectRate; set { _assocCollectRate = value; OnPropertyChanged(); } }

    public ObservableCollection<(string Label, decimal Kg)> ImpactLast6Months { get; } = new();
    public ObservableCollection<(string Label, decimal Kg)> AssocLast6Months { get; } = new();
    public ObservableCollection<(string Business, decimal Kg)> AssocTopBusinesses { get; } = new();

    public event PropertyChangedEventHandler? PropertyChanged;

    public DashboardPage()
    {
        InitializeComponent();
        DataContext = this;
        Loaded += async (_, _) => await LoadAsync();
    }

    protected override void OnInitialized(EventArgs e)
    {
        base.OnInitialized(e);
        Opacity = 0;
        var anim = new DoubleAnimation(0, 1, TimeSpan.FromMilliseconds(250));
        BeginAnimation(OpacityProperty, anim);
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        try
        {
            TotalUsers = await _stats.GetUsersCountAsync();
            PendingApprovals = await _stats.GetPendingApprovalsAsync();
            ProductsCount = await _stats.GetProductsCountAsync();
            TotalMealsDonated = await _stats.GetTotalDonatedMealsAsync();
            await LoadRecentActivityAsync();
            await LoadImpactAsync();
        }
        catch (Exception)
        {
            // Keep UI alive even if DB is down.
        }
    }

    private async Task LoadImpactAsync()
    {
        if (!Session.IsBusiness && !Session.IsAssociation)
            return;

        ImpactLoading = true;
        ImpactError = false;
        ImpactEmpty = false;

        try
        {
            if (Session.IsBusiness)
                await LoadBusinessImpactAsync();
            else if (Session.IsAssociation)
                await LoadAssociationImpactAsync();

            var total = Session.IsBusiness ? ImpactTotalKg : AssocReceivedKg;
            ImpactEmpty = total <= 0.0001m;
        }
        catch (Exception ex)
        {
            ImpactError = true;
            ImpactErrorText = "Impossible de charger les statistiques. " + ex.Message;
        }
        finally
        {
            ImpactLoading = false;
        }
    }

    private static string MonthLabel(DateTime dt) =>
        dt.ToString("MMMM", CultureInfo.GetCultureInfo("fr-FR"));

    private static DateTime StartOfMonth(DateTime dt) => new(dt.Year, dt.Month, 1);

    private async Task LoadBusinessImpactAsync()
    {
        if (!Session.LinkedBusinessId.HasValue)
            throw new Exception("Établissement non lié à la session.");

        var businessId = Session.LinkedBusinessId.Value;
        await using var conn = await DatabaseService.GetConnectionAsync();

        // Donated kg for this business (Accepted/Collected)
        const string donatedSql = @"SELECT COALESCE(SUM(d.quantite), 0)
                                    FROM donations d
                                    LEFT JOIN produits p ON p.id = d.produit_id
                                    LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                    JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                    WHERE b.id = @bid AND d.status IN ('Accepted','Collected')";
        await using (var cmd = new MySqlCommand(donatedSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            ImpactDonatedKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        // Sold quantity (commandes_details) for this business on confirmed/delivered/in-progress statuses.
        const string soldSql = @"SELECT COALESCE(SUM(cd.quantite), 0)
                                 FROM commandes_details cd
                                 JOIN commandes c ON c.id = cd.commande_id
                                 JOIN produits p ON p.id = cd.produit_id
                                 WHERE p.business_id = @bid
                                   AND c.status IN ('livree','confirmee','en_cours_de_livraison','en_cours')";
        await using (var cmd = new MySqlCommand(soldSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            ImpactSoldKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        // Donations accepted count.
        const string donationsCountSql = @"SELECT COUNT(*)
                                           FROM donations d
                                           LEFT JOIN produits p ON p.id = d.produit_id
                                           LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                           JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                           WHERE b.id = @bid AND d.status IN ('Accepted','Collected')";
        await using (var cmd = new MySqlCommand(donationsCountSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            ImpactDonationsCount = Convert.ToInt32(await cmd.ExecuteScalarAsync());
        }

        ImpactTotalKg = ImpactDonatedKg + ImpactSoldKg;
        ImpactMeals = (int)Math.Round((double)(ImpactTotalKg * 2m), MidpointRounding.AwayFromZero);
        ImpactCo2 = Math.Round(ImpactTotalKg * 2.5m, 1);

        // Month totals (current month).
        var now = DateTime.Now;
        var monthStart = StartOfMonth(now);
        var nextMonth = monthStart.AddMonths(1);

        const string donatedMonthSql = @"SELECT COALESCE(SUM(d.quantite), 0)
                                         FROM donations d
                                         LEFT JOIN produits p ON p.id = d.produit_id
                                         LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                         JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                         WHERE b.id = @bid AND d.status IN ('Accepted','Collected')
                                           AND d.date_donation >= @from AND d.date_donation < @to";
        decimal monthDon;
        await using (var cmd = new MySqlCommand(donatedMonthSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            cmd.Parameters.AddWithValue("@from", monthStart);
            cmd.Parameters.AddWithValue("@to", nextMonth);
            monthDon = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        const string soldMonthSql = @"SELECT COALESCE(SUM(cd.quantite), 0)
                                      FROM commandes_details cd
                                      JOIN commandes c ON c.id = cd.commande_id
                                      JOIN produits p ON p.id = cd.produit_id
                                      WHERE p.business_id = @bid
                                        AND c.status IN ('livree','confirmee','en_cours_de_livraison','en_cours')
                                        AND c.date_commande >= @from AND c.date_commande < @to";
        decimal monthSold;
        await using (var cmd = new MySqlCommand(soldMonthSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            cmd.Parameters.AddWithValue("@from", monthStart);
            cmd.Parameters.AddWithValue("@to", nextMonth);
            monthSold = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        ImpactMonthKg = monthDon + monthSold;
        ImpactGoalHit = ImpactMonthKg >= 50m;

        // Estimation flag: any storage-based donations with non-kg unit in Accepted/Collected.
        const string estimationSql = @"SELECT COUNT(*)
                                       FROM donations d
                                       JOIN storage_items si ON si.id = d.storage_item_id
                                       JOIN business b ON b.id = si.business_id
                                       WHERE b.id = @bid
                                         AND d.status IN ('Accepted','Collected')
                                         AND si.unit IS NOT NULL
                                         AND LOWER(si.unit) <> 'kg'";
        await using (var cmd = new MySqlCommand(estimationSql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            ImpactIsEstimation = Convert.ToInt32(await cmd.ExecuteScalarAsync()) > 0;
        }

        // Last 6 months bars (donations + sales).
        ImpactLast6Months.Clear();
        var months = new List<DateTime>();
        for (var i = 5; i >= 0; i--)
            months.Add(StartOfMonth(DateTime.Now).AddMonths(-i));

        // Preload donated per month.
        const string donated6Sql = @"SELECT YEAR(d.date_donation) AS y, MONTH(d.date_donation) AS m, COALESCE(SUM(d.quantite),0) AS total
                                     FROM donations d
                                     LEFT JOIN produits p ON p.id = d.produit_id
                                     LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                     JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                     WHERE b.id=@bid AND d.status IN ('Accepted','Collected')
                                       AND d.date_donation >= @from
                                     GROUP BY YEAR(d.date_donation), MONTH(d.date_donation)";
        var from6 = months[0];
        var donatedMap = new Dictionary<(int Y, int M), decimal>();
        await using (var cmd = new MySqlCommand(donated6Sql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            cmd.Parameters.AddWithValue("@from", from6);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var y = Convert.ToInt32(r["y"]);
                var m = Convert.ToInt32(r["m"]);
                var total = Convert.ToDecimal(r["total"]);
                donatedMap[(y, m)] = total;
            }
        }

        const string sold6Sql = @"SELECT YEAR(c.date_commande) AS y, MONTH(c.date_commande) AS m, COALESCE(SUM(cd.quantite),0) AS total
                                  FROM commandes_details cd
                                  JOIN commandes c ON c.id = cd.commande_id
                                  JOIN produits p ON p.id = cd.produit_id
                                  WHERE p.business_id=@bid
                                    AND c.status IN ('livree','confirmee','en_cours_de_livraison','en_cours')
                                    AND c.date_commande >= @from
                                  GROUP BY YEAR(c.date_commande), MONTH(c.date_commande)";
        var soldMap = new Dictionary<(int Y, int M), decimal>();
        await using (var cmd = new MySqlCommand(sold6Sql, conn))
        {
            cmd.Parameters.AddWithValue("@bid", businessId);
            cmd.Parameters.AddWithValue("@from", from6);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var y = Convert.ToInt32(r["y"]);
                var m = Convert.ToInt32(r["m"]);
                var total = Convert.ToDecimal(r["total"]);
                soldMap[(y, m)] = total;
            }
        }

        foreach (var dt in months)
        {
            var key = (dt.Year, dt.Month);
            donatedMap.TryGetValue(key, out var dkg);
            soldMap.TryGetValue(key, out var skg);
            var total = dkg + skg;
            ImpactLast6Months.Add((MonthLabel(dt), total));
        }

        DrawBusinessCharts();
    }

    private async Task LoadAssociationImpactAsync()
    {
        if (!Session.LinkedAssociationId.HasValue)
            throw new Exception("Association non liée à la session.");

        var associationId = Session.LinkedAssociationId.Value;
        await using var conn = await DatabaseService.GetConnectionAsync();

        const string receivedSql = @"SELECT COALESCE(SUM(quantite),0)
                                     FROM donations
                                     WHERE association_id=@aid AND status IN ('Accepted','Collected')";
        await using (var cmd = new MySqlCommand(receivedSql, conn))
        {
            cmd.Parameters.AddWithValue("@aid", associationId);
            AssocReceivedKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        const string collectedSql = @"SELECT COALESCE(SUM(quantite),0)
                                      FROM donations
                                      WHERE association_id=@aid AND status='Collected'";
        await using (var cmd = new MySqlCommand(collectedSql, conn))
        {
            cmd.Parameters.AddWithValue("@aid", associationId);
            AssocCollectedKg = Convert.ToDecimal(await cmd.ExecuteScalarAsync());
        }

        AssocMeals = (int)Math.Round((double)(AssocCollectedKg * 2m), MidpointRounding.AwayFromZero);
        AssocCo2 = Math.Round(AssocCollectedKg * 2.5m, 1);
        AssocCollectRate = AssocReceivedKg <= 0.0001m ? 0 : (int)Math.Round((double)(AssocCollectedKg / AssocReceivedKg * 100m), MidpointRounding.AwayFromZero);

        // Last 6 months received.
        AssocLast6Months.Clear();
        var months = new List<DateTime>();
        for (var i = 5; i >= 0; i--)
            months.Add(StartOfMonth(DateTime.Now).AddMonths(-i));
        var from6 = months[0];

        const string received6Sql = @"SELECT YEAR(date_donation) AS y, MONTH(date_donation) AS m, COALESCE(SUM(quantite),0) AS total
                                      FROM donations
                                      WHERE association_id=@aid AND status IN ('Accepted','Collected') AND date_donation >= @from
                                      GROUP BY YEAR(date_donation), MONTH(date_donation)";
        var map = new Dictionary<(int Y, int M), decimal>();
        await using (var cmd = new MySqlCommand(received6Sql, conn))
        {
            cmd.Parameters.AddWithValue("@aid", associationId);
            cmd.Parameters.AddWithValue("@from", from6);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var y = Convert.ToInt32(r["y"]);
                var m = Convert.ToInt32(r["m"]);
                var total = Convert.ToDecimal(r["total"]);
                map[(y, m)] = total;
            }
        }
        foreach (var dt in months)
        {
            map.TryGetValue((dt.Year, dt.Month), out var kg);
            AssocLast6Months.Add((MonthLabel(dt), kg));
        }

        // Top businesses donors.
        AssocTopBusinesses.Clear();
        const string topBizSql = @"SELECT b.nom_business AS name, COALESCE(SUM(d.quantite),0) AS total
                                   FROM donations d
                                   LEFT JOIN produits p ON p.id = d.produit_id
                                   LEFT JOIN storage_items si ON si.id = d.storage_item_id
                                   JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                   WHERE d.association_id=@aid AND d.status IN ('Accepted','Collected')
                                   GROUP BY b.id, b.nom_business
                                   ORDER BY total DESC
                                   LIMIT 5";
        await using (var cmd = new MySqlCommand(topBizSql, conn))
        {
            cmd.Parameters.AddWithValue("@aid", associationId);
            await using var r = await cmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
            {
                var name = Convert.ToString(r["name"]) ?? "";
                var kg = Convert.ToDecimal(r["total"]);
                AssocTopBusinesses.Add((name, kg));
            }
        }

        DrawAssociationCharts();
    }

    private void DrawBusinessCharts()
    {
        // These canvases are created in XAML; guard if template not loaded yet.
        var monthly = FindName("BusinessMonthlyCanvas") as Canvas;
        var split = FindName("BusinessSplitCanvas") as Canvas;
        if (monthly is not null)
            DrawVerticalBars(monthly, ImpactLast6Months, ColorFrom("#52B788"));
        if (split is not null)
            DrawSplitBar(split, ImpactDonatedKg, ImpactSoldKg, ColorFrom("#2D6A4F"), ColorFrom("#52B788"));
    }

    private void DrawAssociationCharts()
    {
        var monthly = FindName("AssocMonthlyCanvas") as Canvas;
        var top = FindName("AssocTopCanvas") as Canvas;
        if (monthly is not null)
            DrawVerticalBars(monthly, AssocLast6Months, ColorFrom("#52B788"));
        if (top is not null)
            DrawHorizontalBars(top, AssocTopBusinesses, ColorFrom("#52B788"));
    }

    private static Color ColorFrom(string hex) => (Color)ColorConverter.ConvertFromString(hex)!;

    private static void DrawVerticalBars(Canvas canvas, IEnumerable<(string Label, decimal Kg)> points, Color barColor)
    {
        canvas.Children.Clear();
        var list = new List<(string Label, decimal Kg)>(points);
        if (list.Count == 0)
            return;

        var w = Math.Max(200, canvas.ActualWidth);
        var h = Math.Max(160, canvas.ActualHeight);
        var max = 0.1m;
        foreach (var p in list)
            if (p.Kg > max) max = p.Kg;

        var paddingTop = 18;
        var paddingBottom = 26;
        var paddingSides = 10;
        var plotH = h - paddingTop - paddingBottom;
        var plotW = w - paddingSides * 2;
        var barW = plotW / list.Count * 0.6;
        var gap = plotW / list.Count * 0.4;

        for (var i = 0; i < list.Count; i++)
        {
            var (label, kg) = list[i];
            var x = paddingSides + i * (barW + gap) + gap / 2;
            var frac = (double)(kg / max);
            var bh = Math.Max(2, plotH * frac);
            var y = paddingTop + (plotH - bh);

            var rect = new System.Windows.Shapes.Rectangle
            {
                Width = barW,
                Height = bh,
                Fill = new SolidColorBrush(barColor),
                RadiusX = 3,
                RadiusY = 3
            };
            Canvas.SetLeft(rect, x);
            Canvas.SetTop(rect, y);
            canvas.Children.Add(rect);

            var val = new TextBlock
            {
                Text = $"{kg:0.#}",
                FontSize = 11,
                Foreground = new SolidColorBrush(ColorFrom("#1B2D23"))
            };
            Canvas.SetLeft(val, x);
            Canvas.SetTop(val, y - 16);
            canvas.Children.Add(val);

            var lab = new TextBlock
            {
                Text = label.Length > 3 ? label.Substring(0, 3) : label,
                FontSize = 11,
                Foreground = new SolidColorBrush(ColorFrom("#6B7C72"))
            };
            Canvas.SetLeft(lab, x);
            Canvas.SetTop(lab, h - paddingBottom + 6);
            canvas.Children.Add(lab);
        }
    }

    private static void DrawSplitBar(Canvas canvas, decimal donatedKg, decimal soldKg, Color donatedColor, Color soldColor)
    {
        canvas.Children.Clear();
        var w = Math.Max(240, canvas.ActualWidth);
        var h = Math.Max(40, canvas.ActualHeight);
        var total = donatedKg + soldKg;
        if (total <= 0.0001m)
            total = 1m;

        var pad = 6;
        var barH = 14;
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

    private static void DrawHorizontalBars(Canvas canvas, IEnumerable<(string Business, decimal Kg)> points, Color barColor)
    {
        canvas.Children.Clear();
        var list = new List<(string Business, decimal Kg)>(points);
        if (list.Count == 0)
            return;

        var w = Math.Max(320, canvas.ActualWidth);
        var rowH = 28;
        var h = Math.Max(rowH * list.Count + 10, canvas.ActualHeight);
        canvas.Height = h;

        var max = 0.1m;
        foreach (var p in list)
            if (p.Kg > max) max = p.Kg;

        var leftLabelW = 150;
        var rightW = w - leftLabelW - 40;
        for (var i = 0; i < list.Count; i++)
        {
            var (name, kg) = list[i];
            var y = 8 + i * rowH;

            var lab = new TextBlock { Text = name, FontSize = 12, Foreground = new SolidColorBrush(ColorFrom("#1B2D23")) };
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

    private async System.Threading.Tasks.Task LoadRecentActivityAsync()
    {
        RecentActivity.Clear();
        var orders = await _orders.GetAllAsync();
        foreach (var o in orders.GetRange(0, Math.Min(5, orders.Count)))
            RecentActivity.Add($"Commande #{o.Id} - {o.ClientName} - {o.Total:F2} TND - {o.Status}");

        var donations = await _donations.GetAllAsync();
        foreach (var d in donations.GetRange(0, Math.Min(5, donations.Count)))
            RecentActivity.Add($"Don #{d.Id} - {d.Quantite} portions ({d.ProduitNom}) vers {d.AssociationNom}");
    }

    private void OnPropertyChanged([CallerMemberName] string? name = null) =>
        PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));
}

