document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from PHP (passed via data attributes or global variables)
    const labels = window.branchChartLabels || [];
    const data = window.branchChartData || [];
    const colors = ['#3498db','#2ecc71','#f1c40f','#e74c3c','#9b59b6'];

    // Function to detect theme
    function isDarkMode() {
        return document.body.classList.contains("dark-mode");
    }
    
    function chartTextColor() {
        return isDarkMode() ? "#fff" : "#333";
    }

    // Mobile charts initialization
    function createBarChartMobile() {
        const el = document.getElementById('barChartMobile');
        if (el) {
            new Chart(el, {
                type: 'bar',
                data: { 
                    labels, 
                    datasets: [{ 
                        label: 'Sold', 
                        data, 
                        backgroundColor: colors 
                    }] 
                },
                options: { 
                    responsive: true, 
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: chartTextColor() } },
                        y: { ticks: { color: chartTextColor() } }
                    }
                }
            });
        }
    }
    
    function createDonutChartMobile() {
        const el = document.getElementById('donutChartMobile');
        if (el) {
            new Chart(el, {
                type: 'doughnut',
                data: { 
                    labels, 
                    datasets: [{ 
                        data, 
                        backgroundColor: colors 
                    }] 
                },
                options: { 
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
            
            // Custom legend for donut (mobile)
            const legendContainer = document.getElementById("donutLegendListMobile");
            if (legendContainer) {
                legendContainer.innerHTML = '';
                labels.forEach((label, i) => {
                    const li = document.createElement("li");
                    li.innerHTML = `<span class="color-box" style="background:${colors[i]}"></span> <span style="color:${chartTextColor()}">${label} (${data[i]})</span>`;
                    legendContainer.appendChild(li);
                });
            }
        }
    }

    // Desktop Bar Chart
    const barChartEl = document.getElementById('barChart');
    if (barChartEl) {
        new Chart(barChartEl, {
            type: 'bar',
            data: { 
                labels, 
                datasets: [{ 
                    label: 'Sold', 
                    data, 
                    backgroundColor: colors 
                }] 
            },
            options: { 
                responsive: true, 
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: chartTextColor() } },
                    y: { ticks: { color: chartTextColor() } }
                }
            }
        });
    }
    
    // Desktop Donut Chart
    const donutChartEl = document.getElementById('donutChart');
    if (donutChartEl) {
        new Chart(donutChartEl, {
            type: 'doughnut',
            data: { 
                labels, 
                datasets: [{ 
                    data, 
                    backgroundColor: colors 
                }] 
            },
            options: { 
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
        
        // Custom legend for donut (desktop)
        const legendContainer = document.getElementById("donutLegendList");
        if (legendContainer) {
            labels.forEach((label, i) => {
                const li = document.createElement("li");
                li.innerHTML = `<span class="color-box" style="background:${colors[i]}"></span> <span style="color:${chartTextColor()}">${label} (${data[i]})</span>`;
                legendContainer.appendChild(li);
            });
        }
    }

    // Mobile charts (only init if on small screen)
    if (window.innerWidth < 992) {
        createBarChartMobile();
        createDonutChartMobile();
    }
});
