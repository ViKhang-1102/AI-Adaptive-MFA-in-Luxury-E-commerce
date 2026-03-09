document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('security-dashboard');
    if (!root) return;

    const chartLabels = JSON.parse(root.dataset.chartLabels || '[]');
    const chartData = JSON.parse(root.dataset.chartData || '[]');
    const toggleUrl = root.dataset.toggleUrl;
    const csrfToken = root.dataset.csrf;

    const chartCanvas = document.getElementById('riskChart');
    if (chartCanvas && window.Chart) {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Average Risk Score (0-100)',
                    data: chartData,
                    borderColor: '#D4AF37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#0A192F',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [5, 5], color: '#f3f4f6' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    const toggle = document.getElementById('mfaToggle');
    const statusText = document.getElementById('mfaStatusText');
    if (toggle && statusText && toggleUrl && csrfToken) {
        toggle.addEventListener('change', function () {
            const isEnabled = this.checked;
            statusText.innerText = 'Updating...';

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ enabled: isEnabled })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusText.innerText = data.state === 'true' ? 'Active' : 'Disabled';
                    } else {
                        throw new Error('Toggle failed');
                    }
                })
                .catch(error => {
                    console.error('Error toggling MFA state:', error);
                    statusText.innerText = 'Update Failed.';
                    toggle.checked = !isEnabled;
                });
        });
    }

    document.querySelectorAll('.details-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const auditId = btn.dataset.auditId;
            const row = document.getElementById(`audit-details-${auditId}`);
            if (!row) return;
            const isHidden = row.classList.contains('hidden');
            row.classList.toggle('hidden');
            btn.textContent = isHidden ? 'Hide' : 'View';
        });
    });
});
