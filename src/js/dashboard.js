// JavaScript source code

function dashboard(data, categories, container, title, name) {

    Highcharts.chart(container, {
        chart: {
            type: 'line'
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: categories
        },
        yAxis: {
            title: {
                text: title
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
        series: [{
            name: name,
            data: data,
            color: '#67c3d0'
        }]
    });
}