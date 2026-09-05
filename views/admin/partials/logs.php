<?php
declare(strict_types=1);
?>
<div class="tab-pane fade" id="logsTabContent" role="tabpanel" aria-labelledby="logs-tab">
    <div class="admin-panel-card">
        <div class="panel-header-row flex-wrap gap-3">
            <div>
                <h3 class="panel-title">📜 Sistem Günlükleri (Loglar)</h3>
                <p class="panel-subtitle">PSR-3 Monolog altyapısı ile kaydedilen günlük sistem hareketleri, güvenlik olayları ve hata raporları</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 btn-sm" id="refreshLogsBtn" style="border-radius: var(--radius-md); padding: 7px 14px; font-weight: 600;">
                    <span>🔄</span> Yenile
                </button>
                <a href="/admin/logs/download" id="downloadLogBtn" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 btn-sm" style="border-radius: var(--radius-md); padding: 7px 14px; font-weight: 600;" download>
                    <span>📥</span> Günlüğü İndir
                </a>
            </div>
        </div>

        <!-- Filtre ve Kontrol Çubuğu -->
        <div class="log-filter-toolbar p-3 mb-3 rounded-3" style="background: var(--bg-body); border: 1px solid var(--border-color);">
            <div class="row g-2 align-items-center">
                <!-- Log Dosyası Seçimi -->
                <div class="col-12 col-md-4">
                    <label for="logFileSelect" class="form-label small fw-bold mb-1">Log Dosyası</label>
                    <select id="logFileSelect" class="form-select form-select-sm" style="border-radius: var(--radius-sm);">
                        <option value="">En Güncel Günlük (Bugün)</option>
                    </select>
                </div>

                <!-- Seviye Filtresi -->
                <div class="col-6 col-md-3">
                    <label for="logLevelFilter" class="form-label small fw-bold mb-1">Seviye / Kanal</label>
                    <select id="logLevelFilter" class="form-select form-select-sm" style="border-radius: var(--radius-sm);">
                        <option value="ALL">Tüm Seviyeler</option>
                        <option value="INFO">INFO (Bilgi)</option>
                        <option value="WARNING">WARNING (Uyarı)</option>
                        <option value="ERROR">ERROR (Hata)</option>
                        <option value="CRITICAL">CRITICAL (Kritik)</option>
                        <option value="AUDIT">AUDIT (Denetim)</option>
                        <option value="SECURITY">SECURITY (Güvenlik)</option>
                    </select>
                </div>

                <!-- Arama Kutusu -->
                <div class="col-6 col-md-5">
                    <label for="logSearchInput" class="form-label small fw-bold mb-1">Arama</label>
                    <input type="text" id="logSearchInput" class="form-control form-control-sm" placeholder="Mesaj veya IP içinde ara..." style="border-radius: var(--radius-sm);">
                </div>
            </div>
        </div>

        <!-- Log İstatistik & Durum Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-2 px-1 text-muted small">
            <div>
                Görüntülenen: <strong id="logCountBadge" class="text-dark">0</strong> kayıt
            </div>
            <div id="logFileMeta" class="text-truncate">
                <!-- Seçili dosya adı ve boyutu -->
            </div>
        </div>

        <!-- Log Akış Terminali / Tablosu -->
        <div class="table-responsive log-viewer-container" style="max-height: 600px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <table class="table table-hover align-middle mb-0" id="logTable" style="font-size: 0.82rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                <thead class="sticky-top bg-light text-muted" style="border-bottom: 2px solid var(--border-color); font-family: inherit;">
                <tr>
                    <th style="width: 155px;">Zaman</th>
                    <th style="width: 90px;">Seviye</th>
                    <th style="width: 95px;">Kanal</th>
                    <th>Mesaj ve Bağlam Bilgisi</th>
                </tr>
                </thead>
                <tbody id="logTableBody">
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <em>Günlükler yükleniyor...</em>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
