// Default semua font di setiap tempat di Chart JS
Chart.defaults.font.family = window.getComputedStyle(document.body).getPropertyValue('font-family') ?? "Arial, sans-serif";
Chart.defaults.font.size = 11;
Chart.defaults.font.weight = "400";

// Default options.legends
Chart.defaults.plugins.legend.display = false;
Chart.defaults.plugins.legend.align = "start";
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.legend.labels.boxHeight = 8;
Chart.defaults.plugins.legend.labels.padding = 24;
Chart.defaults.plugins.legend.labels.color = '#636366';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.font = {
    size: 12,
}

// Default options.plugins.tooltip
Chart.defaults.plugins.tooltip.enabled = true;
Chart.defaults.plugins.tooltip.usePointStyle = true;
Chart.defaults.plugins.tooltip.yAlign = "bottom";
Chart.defaults.plugins.tooltip.bodySpacing = 4;
Chart.defaults.plugins.tooltip.boxPadding = 8;
Chart.defaults.plugins.tooltip.titleMarginBottom = 2;
Chart.defaults.plugins.tooltip.padding.top = 6;
Chart.defaults.plugins.tooltip.padding.bottom = 6;
Chart.defaults.plugins.tooltip.padding.left = 12;
Chart.defaults.plugins.tooltip.padding.right = 12;
Chart.defaults.plugins.tooltip.backgroundColor = '#2E3137';
Chart.defaults.plugins.tooltip.callbacks.label = function (context) {
    let label = context.dataset.label || "";

    if (label) {
        label += ": ";
    }
    if (typeof context.parsed != 'number' && context.parsed.y !== null) {
        label +=
            " " +
            context.parsed.y.toLocaleString("id-ID", {
                maximumFractionDigits: 2,
            }).toLowerCase();
    } else if (typeof context.parsed == 'number') {
        label +=
            " " +
            context.parsed.toLocaleString("id-ID", {
                maximumFractionDigits: 2,
            }).toLowerCase();
    }
    return label;
}
Chart.defaults.plugins.tooltip.callbacks.labelPointStyle.pointStyle = "circle";
Chart.defaults.plugins.tooltip.callbacks.labelPointStyle.rotation = 0;
Chart.defaults.plugins.tooltip.callbacks.labelTextColor = function (context) { return "#E5E5EA"; };

// Default Doughnut options
Chart.overrides.doughnut.cutout = "63%";
Chart.overrides.doughnut.borderRadius = 4;
Chart.overrides.doughnut.responsive = true;
Chart.overrides.doughnut.layout = {
    padding: {
        top: 20,
        bottom: 20,
        left: 36,
        right: 36,
    },
}
Chart.overrides.doughnut.plugins.legend.display = false;

// Default Line options
Chart.overrides.line.maintainAspectRatio = false;
Chart.overrides.line.responsive = true;
Chart.overrides.line.borderWidth = 2;
Chart.overrides.line.pointStyle = "circle";
Chart.overrides.line.pointRadius = 5;
Chart.overrides.line.pointBorderWidth = 2;
Chart.overrides.line.pointBorderColor = "#FFF";
Chart.overrides.line.pointHoverRadius = 10;
Chart.overrides.line.hoverBorderWidth = 2;
Chart.overrides.line.hoverBorderColor = "FFF";
Chart.overrides.line.interaction = {
    interaction: false,
    mode: "index",
}


// Default Bar options
Chart.overrides.bar.maintainAspectRatio = false;
Chart.overrides.bar.responsive = true;
Chart.overrides.bar.borderRadius = {
    topLeft: 4,
    topRight: 4,
    bottomRight: 4,
    bottomLeft: 4,
};
Chart.overrides.bar.borderSkipped = false;
Chart.overrides.bar.borderWidth = 1;
Chart.overrides.bar.borderColor = "transparent";
Chart.overrides.bar.barPercentage = 1;
Chart.overrides.bar.categoryPercentage = 40/72;
Chart.overrides.bar.interaction = {
    mode: "index",
}
// Plugins pembantu
/**
 * Fungsi untuk menambahkan jarak dari legend ke area di bawahnya
 * @param1 {number} [space=28] -  jarak (Default 28)
 * @returns {object} - plugin legendMargin
 */
const qnPluginChartAddMarginBottomLegend = (space = 28) => {
    return {
        id: 'legendMargin',
        beforeInit(chart, legend, options) {
            const fitValue = chart.legend.fit;
            
            chart.legend.fit = function fit() {
                fitValue.bind(chart.legend)();
                
                return this.height += space;
            }
        }
    }
}
/**
 * Fungsi untuk mengatur tooltip line
 * @returns {object} - plugin tooltipLine
 */
const qnChartPluginTooltipLineTopToBottom = () => {
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
                ctx.beginPath();
                ctx.setLineDash([5, 3]);
                ctx.moveTo(activePoint.element.x, activePoint.element.y);
                ctx.lineTo(activePoint.element.x, chart.chartArea.bottom);
                ctx.lineWidth = 1;
                ctx.strokeStyle = "#8E8E93";
                ctx.stroke();
                ctx.restore();
            }
        },
    }
}
/**
 * Fungsi untuk meng-set top datalabels
 * @returns {object} - plugin datalabels
 */
const qnChartPluginTopDatalabels = () => {
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


// Variabel pembantu
/**
 * Variabel ini adalah:
 *  1. Dipakai setelah plugins: [ChartDataLabels, ...]
 *  2. Dipakai di chart tipe Doughnut
 *  3. Di sematkan di options.plugins.datatabels
 */
const qnChartConfigDoughnutDatalabels = {
    anchor: "end",
    align: "end",
    offset: 0,
    font: {
        size: 13,
        weight: "500",
    },
    color: (context) => {
        return context.dataset.backgroundColor;
    },
    formatter: (value, context) => {
        const datapoints = context.dataset.data;
        const sumTotal = (total, datapoint) => {
            return total + datapoint;
        };
        const totalVal = datapoints.reduce(sumTotal, 0);
        return (value / totalVal).toLocaleString("id-ID", {
            style: "percent",
        });
    },
}
/**
 * Variabel ini adalah:
 *  1. Dipakai setelah plugins: [ChartDataLabels, ...]
 *  2. Dipakai di chart tipe Bar
 *  3. Di sematkan di options.plugins.datatabels
 */
const qnChartConfigBarDatalabels = {
    display: false,
    color: "#636366",
    // formatter: Math.round,
    anchor: "end",
    offset: -26,
    align: "start",
    font: {
        size: 12
    },
}
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk config scale.x
 *  2. Dipakai di chart tipe Line, Bar, dan sejenisnya
 *  3. Di sematkan di options.scales.x
 * @param1 {boolean} [stacked=false] - status stacked (Default false)
 * @returns {object} - config scale X
 */
const qnChartConfigScaleX = (stacked = false) => {
    return {
        grid: {
            display: false,
        },
        border: {
            display: false,
        },
        ticks: {
            padding: 10,
        },
        stacked: stacked,
    }
}
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk config scale.y
 *  2. Dipakai di chart tipe Line, Bar, dan sejenisnya
 *  3. Di sematkan di options.scales.y
 * @param1 {boolean} [stacked=false] - status stacked (Default false)
 * @returns {object} - config scale Y
 */
const qnChartConfigScaleY = (stacked = false) => {
    return {
        beginAtZero: true,
        grid: {
            color: "#E5E5EA",
        },
        border: {
            dash: qnChartDash,
            display: false,
        },
        ticks: {
            maxTicksLimit: 6,
            callback: function (value, index, array) {
                return value.toLocaleString("id-ID", {
                    notation: "compact",
                }).toLowerCase();
            },
        },
        stacked: stacked,
    }
}
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk dash line
 *  2. Dipakai ketika membutuhkan dash saja
 */
const qnChartDash = [6, 4]
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk config scales
 *  2. Dipakai di chart tipe Line
 *  3. Di sematkan di options.scales
 */
const qnChartConfigLineScales = {
    x: qnChartConfigScaleX(),
    y: qnChartConfigScaleY(),
}
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk config scales
 *  2. Dipakai di chart tipe Bar
 *  3. Di sematkan di options.scales
 */
const qnChartConfigBarScales = {
    x: qnChartConfigScaleX(true),
    y: qnChartConfigScaleY(true),
}
/**
 * Variabel ini adalah:
 *  1. Variable pembantu untuk config options
 *  2. Dipakai di chart tipe Line (khususnya yang filled)
 *  3. Di sematkan di options
 */
const qnChartConfigLineFilledOptions = {
    maintainAspectRatio: false,
    layout: {
        padding: {
            top: 0,
            left: -10,
            right: -10,
            bottom: -100,
        },
    },
    pointRadius: 0,
    pointBorderWidth: 0,
    hover: { mode: null },
    plugins: {
        filler: {
            propagate: false,
        },
        legend: {
            display: false,
        },
        title: {
            display: false,
        },
        tooltip: false,
    },
    pointBackgroundColor: "#fff",
    radius: 10,
    interaction: {
        intersect: false,
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                mirror: true,
                min: 0,
                display: false,
            },
            grid: {
                display: false,
            },
            border: {
                display: false,
            },
            type: "linear",
        },
        x: {
            borderWidth: 10,
            border: {
                color: "#E5E5EA",
                dash: [3, 2],
                display: false,
            },
            ticks: {
                display: false,
            },
        },
    },
    elements: {
        point: {
            radius: 0,
        },
    },
}

// Function pembantu
/**
 * Function ini adalah:
 *  1. Variable pembantu untuk mengkonversi datasets dan labels line filled
 *  2. Dipakai di chart tipe Line (khusu untuk filled)
 *  3. Di sematkan sebelum chart diinisialisasi
//  * @param1 {object} [data={}] - data yang berisi object sudah terstruktur (Default {})
//  * @param2 {boolean} [extraSpace=true] - status extra space (Default true)
//  * @param3 {number} [extraSpaceRatio=360/236] - ratio 'panjang chart + extra space' / 'panjang chart' (Default 360/236)
//  * @returns {object} - config scale Y
 */
const qnChartFunctionLineFilledConverter = (data = {}, extraSpace = true, extraSpaceRatio = 360/236) => {
    let datasets = [];
    let labels = [];
    
    let datasetsLength = data.datasets.length;
    let extraSpaceIndex = 0;
    let dataTempIndex = 0;

    if (extraSpace) {
        datasetsLength++;
        dataTempIndex++;
    }
    // Proses memasukkan datasets
    for (let i = 0; i < datasetsLength; i++) {
        if (extraSpace && i == extraSpaceIndex) {
            var tempData = [];
            var max = 0;
            data.datasets.forEach(function (val, i) {
                tempData.push(null);
                max = max > val.data ? max : val.data;
            });
            tempData[0] = tempData[1] = Math.round((max * (extraSpaceRatio * 100)) / 100);
            tempData.push(null);
        
            datasets = [
                ...datasets,
                {
                    data: tempData,
                    borderColor: ["transparent"],
                    borderWidth: 0,
                },
            ];
        } else {
            let tempIndex = i - dataTempIndex;
            let tempData = [];
            data.datasets.forEach(function (val, i) {
                tempData.push(null);
            });
            tempData.push(null);
            tempData[tempIndex] = data.datasets[tempIndex].data;
            tempData[tempIndex + 1] =
            data.datasets.length + dataTempIndex - 1 == i
                ? data.datasets[tempIndex].data
                : data.datasets[tempIndex + 1].data;
        
            datasets = [
                ...datasets,
                {
                    data: tempData,
                    borderColor: [data.borderColor],
                    backgroundColor: [data.datasets[tempIndex].backgroundColor],
                    fill: true,
                },
            ];
            labels.push(data.datasets[tempIndex].label);
            if (data.datasets.length + dataTempIndex - 1 == i) {
                labels.push(data.datasets[tempIndex].label);
            }
        }
    }

    return {
        datasets: datasets,
        labels: labels,
    }
}