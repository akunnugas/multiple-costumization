// Functional Color
const qnFuncColorBlue = "#2486FF";
const qnFuncColorBlue300 = "#00A6FF";
const qnFuncColorBlue200 = "#29D1FF";
const qnFuncColorCyan = "#11CBDC";
const qnFuncColorOrange = "#F57F16";
const qnFuncColorYellow = "#FFB224";
const colorBlue = "#0F6AF5";

// [START] Chart 1 - Ringkasan Pendananaan Penelitian
let chart1Height = "270px";
document.getElementById("chart-line").style.height = (chart1Height);
document.getElementById("chart-line").style.maxHeight = chart1Height;
// Custom Line Under Tooltip
const customUnderTooltipLine = () => {
    return {
        id: "tooltipLine",
        beforeDraw: (chart) => {
            if (chart.tooltip._active && chart.tooltip._active.length > 0) {
                const ctx = chart.ctx;
                ctx.save();
                let activePoint = chart.tooltip._active[0];
                for (let i = 0; i < chart.tooltip._active.length; i++) {
                    if (
                        activePoint.element.y >
                        chart.tooltip._active[i].element.y
                    ) {
                        activePoint = chart.tooltip._active[i];
                    }
                }

                // Create a linear gradient
                const gradient = ctx.createLinearGradient(
                    activePoint.element.x,
                    activePoint.element.y,
                    activePoint.element.x,
                    chart.chartArea.bottom
                );
                gradient.addColorStop(0, '#ABC9F5'); // Start color
                gradient.addColorStop(1, colorBlue); // End color

                ctx.beginPath();
                ctx.setLineDash([1, 3]);
                ctx.moveTo(activePoint.element.x, activePoint.element.y);
                ctx.lineTo(activePoint.element.x, chart.chartArea.bottom);
                ctx.lineWidth = 2;
                ctx.strokeStyle = gradient;
                ctx.stroke();
                ctx.restore();
            }
        },
    }
}
// Config Tooltip
const getOrCreateTooltip = (chart) => {
    let tooltipEl = chart.canvas.parentNode.querySelector('div');

    if (!tooltipEl) {
      tooltipEl = document.createElement('div');
      tooltipEl.style.fontSize = '12px';
      tooltipEl.style.fontWeight = '600';
      tooltipEl.style.lineHeight = '18px';
      tooltipEl.style.borderRadius = '8px';
      tooltipEl.style.border = '1px solid #E3E8EF';
      tooltipEl.style.background = 'var(--Base-White, #FFF)';
      tooltipEl.style.boxShadow = '0px 6px 10px 0px rgba(177, 177, 177, 0.08), 0px 1px 3px 0px rgba(0, 0, 0, 0.02)';
      tooltipEl.style.color = '#222';
      tooltipEl.style.opacity = 1;
      tooltipEl.style.pointerEvents = 'none';
      tooltipEl.style.position = 'absolute';
      tooltipEl.style.transform = 'translate(-50%, 0)';
      tooltipEl.style.transition = 'all .1s ease';
      tooltipEl.style.display = 'flex';
      tooltipEl.style.gap = '8px';
      tooltipEl.style.alignItems = 'center';


      const table = document.createElement('table');
      table.style.margin = '0px';

      tooltipEl.appendChild(table);
      chart.canvas.parentNode.appendChild(tooltipEl);
    }

    return tooltipEl;
};
// Custom Value and Style Tooltip
const externalTooltipHandler = (context) => {
    // Tooltip Element
    const {chart, tooltip} = context;
    const tooltipEl = getOrCreateTooltip(chart);

    // Hide if no tooltip
    if (tooltip.opacity === 0) {
      tooltipEl.style.opacity = 0;
      return;
    }

    // Set Text
    if (tooltip.body) {
        const titleLines = tooltip.title || [];
        const bodyLines = tooltip.body.map(b => b.lines);

        const footerLineText = 'Dana yang digunakan';

        // Combine title and body lines into a single row
        const bodyLine = `${titleLines.join(': ')}: ${bodyLines.join(' ')}`;

        // Create div for tooltip content
        const bodyLineDiv = document.createElement('div');
        bodyLineDiv.textContent = bodyLine;

        // Create div for footer line
        const footerLineDiv = document.createElement('div');
        footerLineDiv.textContent = footerLineText;
        footerLineDiv.style.color = '#697586';
        footerLineDiv.style.fontWeight = '500';

        // Create wrapper div to contain bodyLineDiv and FooterLine
        const wrapperDiv = document.createElement('div');
        wrapperDiv.style.display = 'flex';
        wrapperDiv.style.flexDirection = 'column';
        wrapperDiv.appendChild(bodyLineDiv);
        wrapperDiv.appendChild(footerLineDiv);

        // Create image element
        const image = document.createElement('img');
        image.src = imageAssetUrl + '/icon/icon-money.svg'; // Set image source
        image.alt = 'Image Alt Text'; // Set image alt text

        // Append image and wrapper div to the tooltip element
        tooltipEl.innerHTML = '';
        tooltipEl.appendChild(image);
        tooltipEl.appendChild(wrapperDiv);
    }

    const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;

    // Display, position, and set styles for font
    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = positionX + tooltip.caretX + 'px';
    tooltipEl.style.top = positionY + tooltip.caretY + (-60) +'px';
    tooltipEl.style.font = tooltip.options.bodyFont.string;
    tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
};
// Config Chart
const getDataRingkasan = () => {
    const dataChartRingkasanEl = document.querySelector('input[name="chart_data_ringkasan"]');
    return JSON.parse(dataChartRingkasanEl.value);
}

let dataRingkasan = getDataRingkasan();
const chartRingkasan = new Chart(document.getElementById("chart-line"), {
    type: "line",
    data: {
        labels: dataRingkasan.labels,
        datasets: [
            {
                data: dataRingkasan.values,
                backgroundColor: colorBlue,
                borderColor: colorBlue,
                fill: true,
                pointBackgroundColor: "rgba(255, 255, 255, 0)",
                pointBorderWidth: 0,
                pointHoverBackgroundColor: "#0F6AF5",
                pointHoverBorderWidth: 3,
                pointHoverRadius: 8,
                pointHoverBorderColor: "rgba(255, 255, 255, 0.30)",
                backgroundColor: (ctx) => {
                    const canvas = ctx.chart.ctx;
                    const gradient = canvas.createLinearGradient(0, 0, 0, 286);

                    // Add color stops
                    gradient.addColorStop(0.078, '#ABC9F5'); // Equivalent to -8.22% in CSS
                    gradient.addColorStop(1, 'rgba(255, 255, 255, 0.00)'); // Equivalent to 100% in CSS

                    return gradient;
                },
            },
        ],
    },
    options: {
        layout: {
            // Jika plugins.tooltip.legend.display == true, maka harus menyesuaikan padding top dari layout dengan cara meminuskannya
            // padding.top masih perlu disesuaikan lagi sesuai dengan space dari legends
            padding: {
                top: -24,
            }
        },
        plugins: {
            tooltip: {
                enabled: false,
                external: externalTooltipHandler,
            },
            legend: {
                display: false,
            },
        },
        // Harus menyisipkan interaction untuk membuat plugins qnChartPluginTooltipLineTopToBottom() bekerja maksimal
        interaction: {
            intersect: false,
            mode: "index",
        },
        // Scales Line default dari quantum
        scales: qnChartConfigLineScales,
    },
    plugins: [qnPluginChartAddMarginBottomLegend(), customUnderTooltipLine()],
});
// [END] Chart 1

// [START] Chart 2 - Chart Status Timeline Penelitian
let chart2Height = "520px";
document.getElementById("chart-bar-horizontal").style.height = (chart2Height);
document.getElementById("chart-bar-horizontal").style.maxHeight = chart2Height;
// Custom data label di atas chart
const customTopDataLabelHorizontalBar = () => {
    return {
        id: "topLabels",
        afterDatasetsDraw: (chart, args, pluginOptions) => {
            const { ctx, scales: {x, y} } = chart;

            chart.data.labels.forEach((label, index) => {
                ctx.font = "12px " + (window.getComputedStyle(document.body).getPropertyValue('font-family') ?? "Instrument Sans");
                ctx.textAlign = "left";
                ctx.fillStyle = "#636366";
                const xPos = 8; // Subtract 24 from the x position
                const yPos = chart.getDatasetMeta(0).data[index].y - 20; // Keep y position as it is
                ctx.fillText(label, xPos, yPos);
            });
        },
    };
};
// Config Chart

const getDataStatusTimeline = () => {
    const dataChartStatusEl = document.querySelector('input[name="data_chart_status"]');
    return JSON.parse(dataChartStatusEl.value);
}

let dataStatusTimeline = getDataStatusTimeline();
const chartStatusTimeline = new Chart(document.getElementById("chart-bar-horizontal"), {
    type: "bar",
    data: {
        labels: ["Pengumpulan Outcome", "Pengumpulan Output", "Pengumpulan Progress Report", "Pelaksanaan Penelitian", "Lolos Pendanaan", "Seleksi Nominasi", "Seleksi Administrasi","Pendaftaran"],
        datasets: [
            {
                data: dataStatusTimeline.count,
                backgroundColor: [qnFuncColorBlue],
                barPercentage: 0.6,
            }
        ],
    },
    options: {
        indexAxis: 'y',
        layout: {
            padding: {
                top: 20,
                left: 0,
            },
        },
        scales: {
            x: {
                ticks: {
                    stepSize: 50,
                },
                border: {
                    color: "#E9E9E9",
                    dash: [3, 2],
                    display: false,
                },
            },
            y: {
                display: false,
            }
        },
        plugins: {
            datalabels: qnChartConfigBarDatalabels,
            tooltip: {
                boxWidth: -10,
                boxHeight: 0,
                callbacks: {
                    labelPointStyle: function(context) {
                        return {
                            pointStyle: false,
                        };
                    },
                    label: function (context) {
                        const index = context.dataIndex;
                        const sublabel = dataStatusTimeline.label[index];

                        let label = context.dataset.label || "";

                        if (sublabel) {
                            label = Object.entries(sublabel).map(([key, val]) => `${key}: ${val}`).join(',\n');
                        }
                        return label;
                    },
                },
            }
        },
    },
    plugins: [customTopDataLabelHorizontalBar()],
});
// [END] Chart 2

// [START] Chart 3 - Chart Status Timeline Penelitian
let chart3Height = "240px";
document.getElementById("chart-bar-sebaran").style.height = (chart3Height);
document.getElementById("chart-bar-sebaran").style.maxHeight = chart3Height;
// Convert nominal suffix ribuan, juta, miliar
function formatData(data) {
    return data.map(number => {
        if (number >= 1000000000) {
            return Math.floor(number / 100000000) / 10 + 'm';
        } else if (number >= 1000000) {
            return Math.floor(number / 100000) / 10 + 'jt';
        } else if (number >= 1000) {
            return Math.floor(number / 100) / 10 + 'rb';
        } else {
            return number.toString(); // Return the number as string
        }
    });
}
// Custom data label di atas chart
const customTopDatalabels = () => {
    return {
        id: "topLabels",
        afterDatasetsDraw: (chart, args, pluginOptions) => {
            let activeLegends = chart.legend.legendItems.filter(function(legend) {
                return legend.hidden === false;
            });

            let datasetIndexList = [];

            activeLegends.forEach(function(legend) {
                const type = chart.data.datasets[legend.datasetIndex].type;
                if (chart.config.type == "bar" && (type == "bar" || type == undefined )) {
                    datasetIndexList.push(legend.datasetIndex);
                }
            });

            const { ctx, scales: {x, y} } = chart;

            chart.data.datasets[0].data.forEach((datapoint, index) => {
                const datasetArray = [];

                chart.data.datasets.forEach((dataset, i) => {
                    if (datasetIndexList.includes(i)) {
                        datasetArray.push(dataset.data[index]);
                    }
                });

                const totalSum = (total, values) => {
                    return total + values;
                }

                let sum = datasetArray.reduce(totalSum, 0);

                // Apply formatData function here
                sum = formatData([sum])[0];

                ctx.font = "12px " + (window.getComputedStyle(document.body).getPropertyValue('font-family') ?? "Arial, sans-serif");
                ctx.textAlign = "center";
                ctx.fillStyle = "#636366";
                if (datasetIndexList.length > 0 ) {
                    ctx.fillText(sum, x.getPixelForValue(index), chart.getDatasetMeta(Math.max(...datasetIndexList)).data[index].y - 12);
                }
            })
        },
    }
}
// Configurasi Chart
const getDataBarSebaran = () => {
    const dataChart = document.querySelector('input[name="chart_data_sebaran"]');
    return JSON.parse(dataChart.value);
}

let dataBarSebaran = getDataBarSebaran();
const chartSebaran = new Chart(document.getElementById("chart-bar-sebaran"), {
    type: "bar",
    data: {
        // labels: ["Perguruan Tinggi", "Dalam Negeri", "Luar Negeri"],
        labels: ["Perguruan Tinggi", "Dana Eksternal"],
        datasets: [
            {
                data: dataBarSebaran,
                backgroundColor: [qnFuncColorBlue],
                barPercentage: 0.6,
            }
        ],
    },
    options: {
        scales: qnChartConfigBarScales,
        plugins: {
            datalabels: qnChartConfigBarDatalabels,
        },
    },
    plugins: [customTopDatalabels()],
});
// [END] Chart 3

const rerender = () => {
    dataStatusTimeline = getDataStatusTimeline()
    chartStatusTimeline.data.datasets[0].data = dataStatusTimeline.count;
    chartStatusTimeline.update();

    dataBarSebaran = getDataBarSebaran();
    chartSebaran.data.datasets[0].data = dataBarSebaran;
    chartSebaran.update();

    dataRingkasan = getDataRingkasan();
    chartRingkasan.labels = dataRingkasan.labels;
    chartRingkasan.data.datasets[0].data = dataRingkasan.values;
    chartRingkasan.update();
}

document.addEventListener('DOMContentLoaded', () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => setTimeout(rerender, 0))
    });
});
