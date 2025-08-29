<?php
// bayi/faturalar.php
session_start();
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php'); exit;
}
/* en üstte */
$EMBED = isset($_GET['embed']) && $_GET['embed'] == '1';
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faturalandırma</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/bayi.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
  

  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Otomatik Faturalandırma</h2>
      <p class="text-gray-600">Abonelik faturaları ve periyodik ödemeler</p>
    </div>

    <!-- Özet kartları -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mb-6">
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aylık Server Hizmetleri</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Aktif Abonelik</span>
            <span class="text-sm font-medium">15 Müşteri</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Aylık Toplam</span>
            <span class="text-sm font-medium">₺18,750</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Sonraki Fatura</span>
            <span class="text-sm font-medium">01.02.2024</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Yıllık Bakım Hizmetleri</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Aktif Sözleşme</span>
            <span class="text-sm font-medium">8 Müşteri</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Yıllık Toplam</span>
            <span class="text-sm font-medium">₺96,000</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Sonraki Fatura</span>
            <span class="text-sm font-medium">15.03.2024</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 sm:col-span-2 lg:col-span-1">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bekleyen Tahsilatlar</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Bekleyen Fatura</span>
            <span class="text-sm font-medium">3 Adet</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Toplam Tutar</span>
            <span class="text-sm font-medium text-red-600">₺12,500</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Ortalama Vade</span>
            <span class="text-sm font-medium">8 Gün</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Otomatik Faturalandırma Listesi -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Otomatik Faturalandırma Listesi</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hizmet Türü</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periyot</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Fatura</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sonraki Fatura</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">ABC Şirketi</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Server Hizmeti</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Aylık</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺1,250</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.01.2024</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.02.2024</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full status-completed">Aktif</span>
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">XYZ Ltd</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Yıllık Bakım</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Yıllık</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺12,000</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15.01.2023</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15.01.2024</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Fatura Kesildi</span>
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">DEF A.Ş</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Server Hizmeti</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Aylık</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺2,100</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.01.2024</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.02.2024</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Ödeme Bekliyor</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <link rel="stylesheet" href="/is-ortaklar-paneli/bayi/bayi.css">
  <script src="/is-ortaklar-paneli/bayi/bayi.js"></script>
</body>
</html>
