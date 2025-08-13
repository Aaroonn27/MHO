<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Analytics Dashboard - 2024</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .year-selector {
            margin-bottom: 20px;
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .year-selector select {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .stat-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-icon {
            float: right;
            font-size: 2rem;
            color: #667eea;
            opacity: 0.7;
        }
        .chart-container {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }
        .chart-container h2 {
            margin-top: 0;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-align: center;
        }
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        .table-container {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }
        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        .data-table tr:hover {
            background: #f5f5f5;
        }
        .highlight-card {
            border-left: 5px solid #667eea;
        }
        .danger-card {
            border-left: 5px solid #e74c3c;
        }
        .success-card {
            border-left: 5px solid #27ae60;
        }
        .warning-card {
            border-left: 5px solid #f39c12;
        }
        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 400px;
        }
        .metric-trend {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 8px;
        }
        .badge-high { background: #e74c3c; color: white; }
        .badge-medium { background: #f39c12; color: white; }
        .badge-low { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fas fa-chart-line"></i> Animal Bite Surveillance Dashboard</h1>
            <div class="year-selector">
                <label for="year" style="color: white; font-weight: 600;">Select Year:</label>
                <select id="year" name="year" onchange="changeYear(this.value)">
                    <option value="2024" selected>2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
        </div>

        <!-- Enhanced Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card highlight-card">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <h3>Total Cases</h3>
                <div class="stat-value">1,247</div>
                <div class="metric-trend">Active in 12 months</div>
            </div>
            <div class="stat-card success-card">
                <i class="fas fa-check-circle stat-icon"></i>
                <h3>Treatment Completion</h3>
                <div class="stat-value">87.3%</div>
                <div class="metric-trend">Complete outcomes</div>
            </div>
            <div class="stat-card warning-card">
                <i class="fas fa-syringe stat-icon"></i>
                <h3>RIG Administration</h3>
                <div class="stat-value">45.2%</div>
                <div class="metric-trend">For high-risk cases</div>
            </div>
            <div class="stat-card danger-card">
                <i class="fas fa-exclamation-triangle stat-icon"></i>
                <h3>High-Risk Cases</h3>
                <div class="stat-value">23.8%</div>
                <div class="metric-trend">Category 3 exposures</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-tint stat-icon"></i>
                <h3>Wound Washing Rate</h3>
                <div class="stat-value">78.5%</div>
                <div class="metric-trend">Immediate first aid</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt stat-icon"></i>
                <h3>Average Response Time</h3>
                <div class="stat-value">2.3</div>
                <div class="metric-trend">Days to first vaccine</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends stat-icon"></i>
                <h3>Gender Distribution</h3>
                <div class="stat-value">56% M</div>
                <div class="metric-trend">44% Female</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-birthday-cake stat-icon"></i>
                <h3>Average Age</h3>
                <div class="stat-value">28.4</div>
                <div class="metric-trend">Years old</div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Monthly Trends</h2>
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Age Group Distribution</h2>
                <canvas id="ageGroupChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Animal Type Analysis</h2>
                <canvas id="animalTypeChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Vaccine Compliance Rate</h2>
                <canvas id="vaccineComplianceChart"></canvas>
            </div>
        </div>

        <!-- Top Barangays Table -->
        <div class="table-container">
            <h2><i class="fas fa-map-marker-alt"></i> Top 10 Barangays with Most Cases</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Barangay</th>
                        <th>Total Cases</th>
                        <th>Percentage</th>
                        <th>High-Risk Cases</th>
                        <th>Completed Treatment</th>
                        <th>Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>BARANGAY POBLACION</td>
                        <td>89</td>
                        <td>7.1%</td>
                        <td>23</td>
                        <td>78</td>
                        <td><span class="badge badge-high">HIGH</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>BARANGAY SAN JOSE</td>
                        <td>76</td>
                        <td>6.1%</td>
                        <td>18</td>
                        <td>68</td>
                        <td><span class="badge badge-medium">MEDIUM</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>BARANGAY SANTA MARIA</td>
                        <td>63</td>
                        <td>5.1%</td>
                        <td>15</td>
                        <td>55</td>
                        <td><span class="badge badge-medium">MEDIUM</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>BARANGAY MALIGAYA</td>
                        <td>52</td>
                        <td>4.2%</td>
                        <td>12</td>
                        <td>47</td>
                        <td><span class="badge badge-low">LOW</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>BARANGAY BAGONG SILANG</td>
                        <td>48</td>
                        <td>3.9%</td>
                        <td>14</td>
                        <td>41</td>
                        <td><span class="badge badge-medium">MEDIUM</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bite Places Analysis -->
        <div class="table-container">
            <h2><i class="fas fa-location-arrow"></i> Most Common Bite Locations</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Location</th>
                        <th>Cases</th>
                        <th>Percentage</th>
                        <th>High-Risk</th>
                        <th>Prevention Priority</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>HOME/RESIDENCE</td>
                        <td>312</td>
                        <td>25.0%</td>
                        <td>78</td>
                        <td><span class="badge badge-high">URGENT</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>STREET/ROAD</td>
                        <td>198</td>
                        <td>15.9%</td>
                        <td>52</td>
                        <td><span class="badge badge-high">URGENT</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>NEIGHBOR'S HOUSE</td>
                        <td>147</td>
                        <td>11.8%</td>
                        <td>34</td>
                        <td><span class="badge badge-medium">MODERATE</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>SCHOOL</td>
                        <td>89</td>
                        <td>7.1%</td>
                        <td>21</td>
                        <td><span class="badge badge-medium">MODERATE</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>MARKET</td>
                        <td>67</td>
                        <td>5.4%</td>
                        <td>18</td>
                        <td><span class="badge badge-low">LOW</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Charts Row 3 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Bite Site Distribution</h2>
                <canvas id="biteSiteChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Treatment Outcome Analysis</h2>
                <canvas id="outcomeChart"></canvas>
            </div>
        </div>

        <!-- Response Time Analysis -->
        <div class="table-container">
            <h2><i class="fas fa-clock"></i> Treatment Response Time Analysis</h2>
            <div class="stats-grid">
                <div class="stat-card success-card">
                    <i class="fas fa-bolt stat-icon"></i>
                    <h3>Within 24 Hours</h3>
                    <div class="stat-value">456</div>
                    <div class="metric-trend">36.6% of cases</div>
                </div>
                <div class="stat-card warning-card">
                    <i class="fas fa-hourglass-half stat-icon"></i>
                    <h3>Within 72 Hours</h3>
                    <div class="stat-value">731</div>
                    <div class="metric-trend">58.6% of cases</div>
                </div>
                <div class="stat-card danger-card">
                    <i class="fas fa-exclamation-circle stat-icon"></i>
                    <h3>Beyond 72 Hours</h3>
                    <div class="stat-value">60</div>
                    <div class="metric-trend">4.8% of cases</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calculator stat-icon"></i>
                    <h3>Average Response</h3>
                    <div class="stat-value">2.3</div>
                    <div class="metric-trend">Days to treatment</div>
                </div>
            </div>
        </div>

        <!-- Animal Status Chart -->
        <div class="chart-container">
            <h2>Animal Status Distribution</h2>
            <canvas id="animalStatusChart"></canvas>
        </div>
    </div>

    <script>
        // Define changeYear function in global scope
        window.changeYear = function(year) {
            window.location.href = '?year=' + year;
        }

        // Sample data - replace with actual PHP data
        const monthlyData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Total Cases',
                data: [89, 76, 94, 112, 98, 87, 103, 91, 85, 76, 68, 94],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'High Risk (Category 3)',
                data: [23, 18, 26, 31, 24, 19, 28, 22, 20, 17, 15, 21],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Age Group Chart
        const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
        new Chart(ageGroupCtx, {
            type: 'doughnut',
            data: {
                labels: ['0-4 years', '5-14 years', '15-29 years', '30-44 years', '45-59 years', '60+ years'],
                datasets: [{
                    data: [89, 234, 312, 287, 198, 127],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB', 
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Animal Type Chart
        const animalTypeCtx = document.getElementById('animalTypeChart').getContext('2d');
        new Chart(animalTypeCtx, {
            type: 'bar',
            data: {
                labels: ['Dog', 'Cat', 'Bat', 'Monkey', 'Others'],
                datasets: [{
                    label: 'Total Cases',
                    data: [1089, 98, 34, 18, 8],
                    backgroundColor: '#4CAF50'
                }, {
                    label: 'High Risk Cases',
                    data: [267, 23, 12, 7, 3],
                    backgroundColor: '#e74c3c'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Vaccine Compliance Chart
        const vaccineComplianceCtx = document.getElementById('vaccineComplianceChart').getContext('2d');
        new Chart(vaccineComplianceCtx, {
            type: 'bar',
            data: {
                labels: ['Day 0', 'Day 3', 'Day 7', 'Day 14', 'Day 28-30'],
                datasets: [{
                    label: 'Compliance Rate (%)',
                    data: [94.2, 89.7, 85.3, 78.9, 71.4],
                    backgroundColor: [
                        '#4CAF50',
                        '#8BC34A', 
                        '#CDDC39',
                        '#FFC107',
                        '#FF9800'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Bite Site Chart
        const biteSiteCtx = document.getElementById('biteSiteChart').getContext('2d');
        new Chart(biteSiteCtx, {
            type: 'pie',
            data: {
                labels: ['Extremities', 'Head/Neck', 'Trunk', 'Multiple Sites', 'Others'],
                datasets: [{
                    data: [756, 198, 167, 89, 37],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56', 
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Outcome Chart
        const outcomeCtx = document.getElementById('outcomeChart').getContext('2d');
        new Chart(outcomeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Complete', 'Incomplete', 'Not Started', 'Died'],
                datasets: [{
                    data: [1089, 98, 47, 13],
                    backgroundColor: [
                        '#4CAF50',
                        '#FF9800',
                        '#f44336',
                        '#9E9E9E'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Animal Status Chart
        const animalStatusCtx = document.getElementById('animalStatusChart').getContext('2d');
        new Chart(animalStatusCtx, {
            type: 'bar',
            data: {
                labels: ['Alive', 'Dead', 'Lost/Unknown'],
                datasets: [{
                    label: 'Number of Animals',
                    data: [892, 234, 121],
                    backgroundColor: [
                        '#4CAF50',
                        '#f44336', 
                        '#FF9800'
                    ]
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>