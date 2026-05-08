<div class="container">
    <div class="px-0">
        <div class="row row-cols-1 row-cols-lg-4 mx-0">
            @foreach ($widgetCount as $item)
            <div @class(['pe-lg-3 pb-lg-0 pb-md-3 pb-2'=> !$loop->last, 'pb-md-0 pb-3 px-0']) wire:ignore>
                <div class="card p-3 w-100 h-100">
                    <div class="d-flex justify-content-between flex-wrap align-items-end">
                        <div class="d-flex flex-column">
                            <div class="d-flex gap-2">
                                <i class="sym sym-{{ $item['icon'] }} fs-4 text-{{ $item['color'] }}"></i>
                                <h4>{{ $item['label'] }}</h4>
                            </div>
                        </div>
                        <span class="fs-2">{{ $item['value'] }}</span>
                    </div>
                    <span class="text-secondary">{{ $item['sublabel'] ?? '' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <br>
    <div class="px-0">
        <div class="row row-cols-1 row-cols-lg-3 mx-0 g-3 ">
            <div class="col px-0">
                <div class="card h-100" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Jenis Dokumen</h4>
                        </div>
                    </div>
                    <div class="p-2" style="height: 280px; position: relative">
                        <canvas id="chart-bar-dokumen" height="280"></canvas>
                    </div>
                </div>
            </div>
            <div class="col ps-lg-3 ps-md-0 pe-lg-3 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card h-100" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4> Ruang Lingkup Mitra</h4>
                        </div>
                    </div>
                    <!-- <div class="p-2" style="height: 280px; position: relative">
                        <canvas id="chart-bar-bentuk-kegiatan" height="280"></canvas>
                    </div> -->
                    <div class="" style="height: 280px; position: relative">
                        <canvas id="chart-pie-ruang-lingkup"></canvas>
                    </div>
                </div>
            </div>
            <div class="col ps-lg-2 ps-md-0 pe-lg-0 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card h-100" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Jenis Mitra</h4>
                        </div>
                    </div>
                    <div class="p-2" style="height: 280px; position: relative">
                        <canvas id="chart-gauge-bar-doughnut"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="px-0 mb-3">
        <div class="row row-cols-1 row-cols-lg-3 mx-0">
            <div class="pb-md-0 col-lg-6 pb-3 px-0 pe-lg-3 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Bentuk Kegiatan Terbanyak</h4>
                        </div>
                    </div>
                    <div class="p-2" style="height: 280px; position: relative">
                        <canvas id="chart-bar-bentuk-kegiatan" height="280"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 pe-lg-2  pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Top 5 Unit Kerja</h4>
                        </div>
                    </div>
                    <div class="" style="height: 280px; position: relative; padding: 0;">
                        <canvas id="chart-bar-unit-kerja" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="px-0 mb-3">
        <div class="row row-cols-1 row-cols-lg-3 mx-0">
            <div class="pb-md-0 pb-3 px-0  pe-lg-3 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Top 5 Provinsi Mitra</h4>
                        </div>
                    </div>
                    <div class="" style="height: 280px; position: relative">
                        <canvas id="chart-bar-wilayah" height="280"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 pe-lg-2   pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Top 5 Kriteria Mitra</h4>
                        </div>
                    </div>
                    <div class="" style="height: 280px; position: relative">
                        <canvas id="chart-bar-kriteria-mitra" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="px-0 mb-3">
        <div class="row row-cols-1 row-cols-lg-3 mx-0">
            <div class="pb-md-0 col-lg-6 pb-3 px-0 pe-lg-3 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Implementasi Kegiatan</h4>
                        </div>
                    </div>
                    <div class="p-2" style="height: 280px; position: relative">
                        <canvas id="chart-pie-implementasi-kegiatan" height="280"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 pe-lg-2 pe-lg-3 pb-lg-0 pb-md-3 pb-2 pb-md-0 pb-3 px-0">
                <div class="card" style="padding: 1.5rem;">
                    <div class="card-title">
                        <div>
                            <h4>Implementasi Kerjasama</h4>
                        </div>
                    </div>
                    <div class="" style="height: 280px; position: relative; padding: 0;">
                        <canvas id="chart-pie-implementasi-kerjasama" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <br>
    @pushOnce('scripts')
    <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/chart.js-4.3.0/dist/chart.umd.js') }}"></script>
    <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js') }}"></script>
    <script type="text/javascript" src="{{ Page::quantumAsset('js/utils/chart-settings.js') }}"></script>
    <script>
        function splitAllLabels(chartLabels) {
            return chartLabels.map(label => {
                const parts = label.split(' ');
                return parts.length > 2 ? parts : label;
            });
        }

        Chart.register(ChartDataLabels);
        document.addEventListener('DOMContentLoaded', function() {
            const qnFuncColorBlue = "#0F6AF5";
            const qnFuncColorNeutral200 = "#369a97";
            const thirdColor = "#286361";

            let chartDoughnut = null;
            let chartBar = null;
            let chartBarKegiatan = null;
            let chartBarUnitKerja = null;
            let chartPieRuangLingkup = null;
            let chartBarKriteriaMitra = null;
            let chartBarWilayah = null;
            let chartPieImplementasiKegiatan = null;
            let chartPieImplementasiKerjasama = null;


            const buildChartDoughnut = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-gauge-bar-doughnut");
                if (!canvasElement) {
                    console.error('Canvas element "chart-gauge-bar-doughnut" not found');
                    return null;
                }

                // Destroy existing chart if it exists
                if (chartDoughnut) {
                    chartDoughnut.destroy();
                }

                try {
                    chartDoughnut = new Chart(canvasElement, {
                        type: "pie",
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: [qnFuncColorBlue, qnFuncColorNeutral200],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: "0%",
                            borderRadius: 10,
                            layout: {
                                padding: 0
                            },
                            rotation: 270,
                            hoverBorderWidth: 0,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    enabled: true
                                },
                                datalabels: {
                                    enabled: true,
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => value.toLocaleString("id-ID"),
                                },
                            },
                            animation: true,
                        },
                    });
                    console.log('Doughnut chart created successfully');
                    return chartDoughnut;
                } catch (error) {
                    console.error('Error creating doughnut chart:', error);
                    return null;
                }
            };

            const buildChartBar = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-bar-dokumen");
                const formattedLabels = splitAllLabels(chartLabels);
                try {
                    chartBar = new Chart(canvasElement, {
                        type: "bar",
                        data: {
                            labels: formattedLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: [qnFuncColorBlue],
                                borderColor: [qnFuncColorBlue],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: {
                                        display: true,
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: {
                                datalabels: {
                                    enabled: true,
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => value.toLocaleString("id-ID"),
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: (context) => context.parsed.y.toLocaleString("id-ID")
                                    },
                                },
                            },
                        },
                    });
                    console.log('Bar chart created successfully');
                    return chartBar;
                } catch (error) {
                    console.error('Error creating bar chart:', error);
                    return null;
                }
            };


            const buildChartBarBentukKegiatan = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-bar-bentuk-kegiatan");
                const chartBarKegiatan = new Chart(canvasElement, {
                    type: "bar",
                    data: {
                        labels: chartData.map((_, i) => i + 1), // Order number
                        datasets: [{
                            data: chartData,
                            backgroundColor: [qnFuncColorBlue],
                            borderColor: [qnFuncColorBlue],
                            barPercentage: 0.7,
                            categoryPercentage: 0.5,
                            barThickness: 22
                        }],
                    },
                    options: {
                        layout: {
                            padding: 0
                        },
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                display: false,
                                max: Math.max(...chartData) * 1.1,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: 'black',
                                    mirror: true,
                                    font: {
                                        size: 14
                                    },
                                    labelOffset: -20,
                                    callback: function(value, index) {
                                        return `${index + 1}. ${chartLabels[index]}`;
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: (context) => {
                                        return `${context[0].label}. ${chartLabels[context[0].dataIndex]}`;
                                    },
                                    label: (context) => {
                                        return context.parsed.x.toLocaleString("id-ID");
                                    }
                                }
                            },
                            datalabels: {
                                color: 'black',
                                font: {
                                    size: 14
                                },
                                anchor: 'end',
                                align: 'right',
                                offset: 5,
                                formatter: (value) => value.toLocaleString("id-ID")
                            },

                        }
                    }
                });
                console.log('Progress bar chart for Unit Kerja created successfully');
                return chartBarKegiatan;
            };

            const buildChartBarUnitKerja = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-bar-unit-kerja");
                const chartBarUnitKerja = new Chart(canvasElement, {
                    type: "bar",
                    data: {
                        labels: chartData.map((_, i) => i + 1), // Order number
                        datasets: [{
                            data: chartData,
                            backgroundColor: [qnFuncColorBlue],
                            borderColor: [qnFuncColorBlue],
                            barPercentage: 0.7,
                            categoryPercentage: 0.5,
                            barThickness: 22
                        }],
                    },
                    options: {
                        layout: {
                            padding: 0
                        },
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                display: false,
                                max: Math.max(...chartData) * 1.1,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: 'black',
                                    mirror: true,
                                    font: {
                                        size: 14
                                    },
                                    labelOffset: -20,
                                    callback: function(value, index) {
                                        return `${index + 1}. ${chartLabels[index]}`;
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: (context) => {
                                        return `${context[0].label}. ${chartLabels[context[0].dataIndex]}`;
                                    },
                                    label: (context) => {
                                        return context.parsed.x.toLocaleString("id-ID");
                                    }
                                }
                            },
                            datalabels: {
                                color: 'black',
                                font: {
                                    size: 14
                                },
                                anchor: 'end',
                                align: 'right',
                                offset: 5,
                                formatter: (value) => value.toLocaleString("id-ID")
                            },

                        }
                    }
                });
                console.log('Progress bar chart for Unit Kerja created successfully');
                return chartBarUnitKerja;
            };

            const buildChartPieRuangLingkup = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-pie-ruang-lingkup");

                try {
                    chartPieRuangLingkup = new Chart(canvasElement, {
                        type: "pie",
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: [qnFuncColorBlue, qnFuncColorNeutral200, thirdColor],
                            }],
                            barPercentage: 0.7,
                            categoryPercentage: 0.5,
                            barThickness: 22
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: "0%",
                            borderRadius: 10,
                            layout: {
                                padding: 0
                            },
                            rotation: 270,
                            hoverBorderWidth: 0,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    enabled: true
                                },
                                datalabels: {
                                    enabled: true,
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => value.toLocaleString("id-ID"),
                                },
                            },
                            animation: true,
                        },
                    });
                    console.log('Doughnut chart created successfully');
                    return chartDoughnut;
                } catch (error) {
                    console.error('Error creating doughnut chart:', error);
                    return null;
                }
            };

            const buildChartPieImplementasiKegiatan = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-pie-implementasi-kegiatan");

                try {
                    chartPieImplementasiKegiatan = new Chart(canvasElement, {
                        type: "pie",
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: [qnFuncColorBlue, qnFuncColorNeutral200, thirdColor],
                            }],
                            barPercentage: 0.7,
                            categoryPercentage: 0.5,
                            barThickness: 22
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: "0%",
                            borderRadius: 10,
                            layout: {
                                padding: 0
                            },
                            rotation: 270,
                            hoverBorderWidth: 0,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    enabled: true
                                },
                                datalabels: {
                                    enabled: true,
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => value.toLocaleString("id-ID"),
                                },
                            },
                            animation: true,
                        },
                    });
                    return chartPieImplementasiKegiatan;
                } catch (error) {
                    return null;
                }
            };


            const buildChartPieImplementasiKerjasama = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-pie-implementasi-kerjasama");

                try {
                    chartPieImplementasiKerjasama = new Chart(canvasElement, {
                        type: "pie",
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: [qnFuncColorBlue, qnFuncColorNeutral200, thirdColor],
                            }],
                            barPercentage: 0.7,
                            categoryPercentage: 0.5,
                            barThickness: 22
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: "0%",
                            borderRadius: 10,
                            layout: {
                                padding: 0
                            },
                            rotation: 270,
                            hoverBorderWidth: 0,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    enabled: true
                                },
                                datalabels: {
                                    enabled: true,
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => value.toLocaleString("id-ID"),
                                },
                            },
                            animation: true,
                        },
                    });
                    return chartPieImplementasiKerjasama;
                } catch (error) {
                    return null;
                }
            };

            const buildChartBarKriteriaMitra = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-bar-kriteria-mitra");
                if (!canvasElement) {
                    console.error('Canvas element "chart-bar-kriteria-mitra" not found');
                    return null;
                }

                const chartBarKriteriaMitra = new Chart(canvasElement, {
                    type: "bar",
                    data: {
                        labels: chartData.map((_, i) => i + 1),
                        datasets: [{
                            data: chartData,
                            backgroundColor: [qnFuncColorBlue],
                            borderColor: [qnFuncColorBlue],
                            barPercentage: 0.7
                        }],
                    },
                    options: {
                        layout: {
                            padding: 0
                        },
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                display: false,
                                max: Math.max(...chartData) * 1.1,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: 'black',
                                    mirror: true,
                                    font: {
                                        size: 14
                                    },
                                    labelOffset: -20,
                                    callback: function(value, index) {
                                        return `${index + 1}. ${chartLabels[index]}`;
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: (context) => {
                                        return `${context[0].label}. ${chartLabels[context[0].dataIndex]}`;
                                    },
                                    label: (context) => {
                                        return context.parsed.x.toLocaleString("id-ID");
                                    }
                                }
                            },
                            datalabels: {
                                color: 'black',
                                font: {
                                    size: 14
                                },
                                anchor: 'end',
                                align: 'right',
                                offset: 5,
                                formatter: (value) => value.toLocaleString("id-ID")
                            },
                        }
                    }
                });

                console.log('Progress bar chart for Kriteria Mitra created successfully');
                return chartBarKriteriaMitra;
            };

            const buildChartBarWilayah = (chartData, chartLabels) => {
                const canvasElement = document.getElementById("chart-bar-wilayah");
                if (!canvasElement) {
                    console.error('Canvas element "chart-bar-bentuk-kegiatan" not found');
                    return null;
                }

                const formattedLabels = splitAllLabels(chartLabels);
                chartBarWilayah = new Chart(canvasElement, {
                    type: "bar",
                    data: {
                        labels: formattedLabels,
                        datasets: [{
                            data: chartData,
                            backgroundColor: [qnFuncColorBlue],
                            borderColor: [qnFuncColorBlue],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: {
                                    display: true,
                                    color: 'black'
                                }
                            },
                            y: {
                                ticks: {
                                    precision: 0, // Ensures only integer values
                                    callback: function(value) {
                                        if (value % 1 === 0) {
                                            return value;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: {
                            datalabels: {
                                enabled: true,
                                color: 'white',
                                font: {
                                    weight: 'bold'
                                },
                                formatter: (value) => value.toLocaleString("id-ID"),
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    label: (context) => context.parsed.y.toLocaleString("id-ID")
                                },
                            },
                        },
                    },
                });
                console.log('Bar chart for Bentuk Kegiatan created successfully');
                return chartBarKegiatan;

            };

            const jenisMitraData = @json($jenisMitra ?? []);
            if (jenisMitraData && jenisMitraData.length > 0) {
                const jenisMitraLabels = jenisMitraData.map(item => item.jenis_mitra);
                const jenisMitraValues = jenisMitraData.map(item => item.jumlah_mitra);
                buildChartDoughnut(jenisMitraValues, jenisMitraLabels);
            }

            const jenisDokumenData = @json($jenisDokumenCount ?? []);
            if (jenisDokumenData && jenisDokumenData.length > 0) {
                const jenisDokumenLabels = jenisDokumenData.map(item => item.jenis_dokumen);
                const jenisDokumenValues = jenisDokumenData.map(item => item.kerjasama_count);
                buildChartBar(jenisDokumenValues, jenisDokumenLabels);
            }

            const bentukKegiatanData = @json($bentukKegiatanCount ?? []);
            if (bentukKegiatanData && bentukKegiatanData.length > 0) {
                const bentukKegiatanLabels = bentukKegiatanData.map(item => item.merge);
                const bentukKegiatanValues = bentukKegiatanData.map(item => item.total);
                buildChartBarBentukKegiatan(bentukKegiatanValues, bentukKegiatanLabels);
            }

            const unitKerjaData = @json($unitKerjaCount ?? []);
            if (unitKerjaData && unitKerjaData.length > 0) {
                const unitKerjaLabels = unitKerjaData.map(item => item.nama_unit);
                const unitKerjaValues = unitKerjaData.map(item => item.total_kerjasama);
                buildChartBarUnitKerja(unitKerjaValues, unitKerjaLabels);
            }

            const ruangLingkupData = @json($lingkupMitra ?? []);
            if (ruangLingkupData && ruangLingkupData.length > 0) {
                const lingkupKerjaLabels = ruangLingkupData.map(item => item.tingkat_mitra);
                const lingkupKerjaValues = ruangLingkupData.map(item => item.jumlah_mitra);
                buildChartPieRuangLingkup(lingkupKerjaValues, lingkupKerjaLabels);
            }

            const kriteriaMitraData = @json($kriteriaMitra ?? []);
            if (kriteriaMitraData && kriteriaMitraData.length > 0) {
                const kriteriaMitraLabels = kriteriaMitraData.map(item => item.klasifikasi_mitra);
                const kriteriaMitraValues = kriteriaMitraData.map(item => item.jumlah_mitra);
                buildChartBarKriteriaMitra(kriteriaMitraValues, kriteriaMitraLabels);
            }


            const provinsiData = @json($provinsi ?? []);
            if (provinsiData && provinsiData.length > 0) {
                const provinsiLabels = provinsiData.map(item => item.provinsi);
                const provinsiValues = provinsiData.map(item => item.jumlah_mitra);
                buildChartBarWilayah(provinsiValues, provinsiLabels);
            }

            const implementasiKerjasamaData = @json($implementasiKerjasama ?? []);
            if (implementasiKerjasamaData && implementasiKerjasamaData.length > 0) {
                const implementasiKerjasamaLabels = implementasiKerjasamaData.map(item => item.label);
                const implementasiKerjasamaValues = implementasiKerjasamaData.map(item => item.jumlah);
                buildChartPieImplementasiKerjasama(implementasiKerjasamaValues, implementasiKerjasamaLabels);
            }

            const implementasiKegiatanData = @json($implementasiKegiatan ?? []);
            if (implementasiKegiatanData && implementasiKegiatanData.length > 0) {
                const implementasiKegiatanLabels = implementasiKegiatanData.map(item => item.label);
                const implementasiKegiatanValues = implementasiKegiatanData.map(item => item.jumlah);
                buildChartPieImplementasiKegiatan(implementasiKegiatanValues, implementasiKegiatanLabels);
            }
        });
    </script>
    @endPushOnce
</div>