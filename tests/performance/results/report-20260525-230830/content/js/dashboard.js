/*
   Licensed to the Apache Software Foundation (ASF) under one or more
   contributor license agreements.  See the NOTICE file distributed with
   this work for additional information regarding copyright ownership.
   The ASF licenses this file to You under the Apache License, Version 2.0
   (the "License"); you may not use this file except in compliance with
   the License.  You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
var showControllersOnly = false;
var seriesFilter = "";
var filtersOnlySampleSeries = true;

/*
 * Add header in statistics table to group metrics by category
 * format
 *
 */
function summaryTableHeader(header) {
    var newRow = header.insertRow(-1);
    newRow.className = "tablesorter-no-sort";
    var cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Requests";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 3;
    cell.innerHTML = "Executions";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 7;
    cell.innerHTML = "Response Times (ms)";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Throughput";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 2;
    cell.innerHTML = "Network (KB/sec)";
    newRow.appendChild(cell);
}

/*
 * Populates the table identified by id parameter with the specified data and
 * format
 *
 */
function createTable(table, info, formatter, defaultSorts, seriesIndex, headerCreator) {
    var tableRef = table[0];

    // Create header and populate it with data.titles array
    var header = tableRef.createTHead();

    // Call callback is available
    if(headerCreator) {
        headerCreator(header);
    }

    var newRow = header.insertRow(-1);
    for (var index = 0; index < info.titles.length; index++) {
        var cell = document.createElement('th');
        cell.innerHTML = info.titles[index];
        newRow.appendChild(cell);
    }

    var tBody;

    // Create overall body if defined
    if(info.overall){
        tBody = document.createElement('tbody');
        tBody.className = "tablesorter-no-sort";
        tableRef.appendChild(tBody);
        var newRow = tBody.insertRow(-1);
        var data = info.overall.data;
        for(var index=0;index < data.length; index++){
            var cell = newRow.insertCell(-1);
            cell.innerHTML = formatter ? formatter(index, data[index]): data[index];
        }
    }

    // Create regular body
    tBody = document.createElement('tbody');
    tableRef.appendChild(tBody);

    var regexp;
    if(seriesFilter) {
        regexp = new RegExp(seriesFilter, 'i');
    }
    // Populate body with data.items array
    for(var index=0; index < info.items.length; index++){
        var item = info.items[index];
        if((!regexp || filtersOnlySampleSeries && !info.supportsControllersDiscrimination || regexp.test(item.data[seriesIndex]))
                &&
                (!showControllersOnly || !info.supportsControllersDiscrimination || item.isController)){
            if(item.data.length > 0) {
                var newRow = tBody.insertRow(-1);
                for(var col=0; col < item.data.length; col++){
                    var cell = newRow.insertCell(-1);
                    cell.innerHTML = formatter ? formatter(col, item.data[col]) : item.data[col];
                }
            }
        }
    }

    // Add support of columns sort
    table.tablesorter({sortList : defaultSorts});
}

$(document).ready(function() {

    // Customize table sorter default options
    $.extend( $.tablesorter.defaults, {
        theme: 'blue',
        cssInfoBlock: "tablesorter-no-sort",
        widthFixed: true,
        widgets: ['zebra']
    });

    var data = {"OkPercent": 94.44444444444444, "KoPercent": 5.555555555555555};
    var dataset = [
        {
            "label" : "FAIL",
            "data" : data.KoPercent,
            "color" : "#FF6347"
        },
        {
            "label" : "PASS",
            "data" : data.OkPercent,
            "color" : "#9ACD32"
        }];
    $.plot($("#flot-requests-summary"), dataset, {
        series : {
            pie : {
                show : true,
                radius : 1,
                label : {
                    show : true,
                    radius : 3 / 4,
                    formatter : function(label, series) {
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'
                            + label
                            + '<br/>'
                            + Math.round10(series.percent, -2)
                            + '%</div>';
                    },
                    background : {
                        opacity : 0.5,
                        color : '#000'
                    }
                }
            }
        },
        legend : {
            show : true
        }
    });

    // Creates APDEX table
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.7916666666666666, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [1.0, 500, 1500, "GET dashboard (after login)-0"], "isController": false}, {"data": [0.5, 500, 1500, "Ruang Ujian"], "isController": false}, {"data": [0.5, 500, 1500, "Simpan soal 1"], "isController": false}, {"data": [0.75, 500, 1500, "Hasil Ujian"], "isController": false}, {"data": [1.0, 500, 1500, "GET dashboard (after login)-1"], "isController": false}, {"data": [1.0, 500, 1500, "Ruang Ujian-1"], "isController": false}, {"data": [0.5, 500, 1500, "Masuk Ujian"], "isController": false}, {"data": [0.75, 500, 1500, "Daftar Ujian"], "isController": false}, {"data": [1.0, 500, 1500, "Ruang Ujian-0"], "isController": false}, {"data": [0.75, 500, 1500, "Selesai Ujian"], "isController": false}, {"data": [1.0, 500, 1500, "Hasil Ujian-1"], "isController": false}, {"data": [1.0, 500, 1500, "Daftar Ujian-1"], "isController": false}, {"data": [1.0, 500, 1500, "Daftar Ujian-0"], "isController": false}, {"data": [1.0, 500, 1500, "Hasil Ujian-0"], "isController": false}, {"data": [1.0, 500, 1500, "Masuk Ujian-1"], "isController": false}, {"data": [0.25, 500, 1500, "POST login"], "isController": false}, {"data": [1.0, 500, 1500, "Masuk Ujian-0"], "isController": false}, {"data": [1.0, 500, 1500, "Mulai Ujian"], "isController": false}, {"data": [0.5, 500, 1500, "GET dashboard (after login)"], "isController": false}, {"data": [0.75, 500, 1500, "GET login page"], "isController": false}]}, function(index, item){
        switch(index){
            case 0:
                item = item.toFixed(3);
                break;
            case 1:
            case 2:
                item = formatDuration(item);
                break;
        }
        return item;
    }, [[0, 0]], 3);

    // Create statistics table
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 36, 2, 5.555555555555555, 419.63888888888886, 241, 1013, 341.0, 677.5000000000005, 918.6499999999999, 1013.0, 1.40597539543058, 37.231459614821325, 1.5149781536809217], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["GET dashboard (after login)-0", 2, 0, 0.0, 298.5, 244, 353, 298.5, 353.0, 353.0, 353.0, 2.1299254526091587, 5.241613418530352, 1.7368044462193823], "isController": false}, {"data": ["Ruang Ujian", 2, 0, 0.0, 585.0, 566, 604, 585.0, 604.0, 604.0, 604.0, 1.7271157167530224, 80.80675194300518, 2.858005451208981], "isController": false}, {"data": ["Simpan soal 1", 2, 1, 50.0, 271.0, 249, 293, 271.0, 293.0, 293.0, 293.0, 2.3391812865497075, 5.365953947368421, 3.090734649122807], "isController": false}, {"data": ["Hasil Ujian", 2, 0, 0.0, 552.5, 459, 646, 552.5, 646.0, 646.0, 646.0, 1.4958863126402393, 68.08181796933434, 1.8559800392670158], "isController": false}, {"data": ["GET dashboard (after login)-1", 2, 0, 0.0, 367.5, 340, 395, 367.5, 395.0, 395.0, 395.0, 1.834862385321101, 85.23957138761467, 1.4890338302752293], "isController": false}, {"data": ["Ruang Ujian-1", 2, 0, 0.0, 324.5, 313, 336, 324.5, 336.0, 336.0, 336.0, 2.2099447513812156, 97.78034357734806, 1.826873273480663], "isController": false}, {"data": ["Masuk Ujian", 2, 0, 0.0, 590.0, 568, 612, 590.0, 612.0, 612.0, 612.0, 1.7953321364452424, 83.99840103231597, 2.9708889699281866], "isController": false}, {"data": ["Daftar Ujian", 2, 0, 0.0, 460.5, 354, 567, 460.5, 567.0, 567.0, 567.0, 1.8744142455482662, 89.29735092549204, 2.2945100164011247], "isController": false}, {"data": ["Ruang Ujian-0", 2, 0, 0.0, 259.5, 252, 267, 259.5, 267.0, 267.0, 267.0, 2.4360535931790497, 6.191239722898904, 2.0173568818514007], "isController": false}, {"data": ["Selesai Ujian", 2, 0, 0.0, 388.5, 253, 524, 388.5, 524.0, 524.0, 524.0, 1.7699115044247788, 4.49823700221239, 2.288440265486726], "isController": false}, {"data": ["Hasil Ujian-1", 1, 0, 0.0, 391.0, 391, 391, 391.0, 391.0, 391.0, 391.0, 2.557544757033248, 110.29661524936061, 2.110473945012788], "isController": false}, {"data": ["Daftar Ujian-1", 1, 0, 0.0, 325.0, 325, 325, 325.0, 325.0, 325.0, 325.0, 3.076923076923077, 132.6953125, 2.5390625], "isController": false}, {"data": ["Daftar Ujian-0", 1, 0, 0.0, 241.0, 241, 241, 241.0, 241.0, 241.0, 241.0, 4.149377593360996, 10.51526841286307, 3.3673171680497926], "isController": false}, {"data": ["Hasil Ujian-0", 1, 0, 0.0, 253.0, 253, 253, 253.0, 253.0, 253.0, 253.0, 3.952569169960474, 10.016520503952568, 3.2732213438735176], "isController": false}, {"data": ["Masuk Ujian-1", 2, 0, 0.0, 326.5, 311, 342, 326.5, 342.0, 342.0, 342.0, 2.333722287047841, 103.25695558634773, 1.929195230455076], "isController": false}, {"data": ["POST login", 2, 1, 50.0, 607.5, 313, 902, 607.5, 902.0, 902.0, 902.0, 2.2148394241417497, 5.223473837209302, 2.921035783499446], "isController": false}, {"data": ["Masuk Ujian-0", 2, 0, 0.0, 261.5, 255, 268, 261.5, 268.0, 268.0, 268.0, 2.5974025974025974, 6.601308847402597, 2.1509740259740258], "isController": false}, {"data": ["Mulai Ujian", 2, 0, 0.0, 297.0, 293, 301, 297.0, 301.0, 301.0, 301.0, 2.3584905660377355, 5.9941129864386795, 3.2083763266509435], "isController": false}, {"data": ["GET dashboard (after login)", 2, 0, 0.0, 669.0, 587, 751, 669.0, 751.0, 751.0, 751.0, 1.4958863126402393, 73.17352865557218, 2.4337369109947646], "isController": false}, {"data": ["GET login page", 2, 0, 0.0, 689.5, 366, 1013, 689.5, 1013.0, 1013.0, 1013.0, 1.5003750937734435, 64.70514112903226, 0.17875562640660164], "isController": false}]}, function(index, item){
        switch(index){
            // Errors pct
            case 3:
                item = item.toFixed(2) + '%';
                break;
            // Mean
            case 4:
            // Mean
            case 7:
            // Median
            case 8:
            // Percentile 1
            case 9:
            // Percentile 2
            case 10:
            // Percentile 3
            case 11:
            // Throughput
            case 12:
            // Kbytes/s
            case 13:
            // Sent Kbytes/s
                item = item.toFixed(2);
                break;
        }
        return item;
    }, [[0, 0]], 0, summaryTableHeader);

    // Create error table
    createTable($("#errorsTable"), {"supportsControllersDiscrimination": false, "titles": ["Type of error", "Number of errors", "% in errors", "% in all samples"], "items": [{"data": ["422/Unprocessable Entity", 1, 50.0, 2.7777777777777777], "isController": false}, {"data": ["403/Forbidden", 1, 50.0, 2.7777777777777777], "isController": false}]}, function(index, item){
        switch(index){
            case 2:
            case 3:
                item = item.toFixed(2) + '%';
                break;
        }
        return item;
    }, [[1, 1]]);

        // Create top5 errors by sampler
    createTable($("#top5ErrorsBySamplerTable"), {"supportsControllersDiscrimination": false, "overall": {"data": ["Total", 36, 2, "422/Unprocessable Entity", 1, "403/Forbidden", 1, "", "", "", "", "", ""], "isController": false}, "titles": ["Sample", "#Samples", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors"], "items": [{"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["Simpan soal 1", 2, 1, "403/Forbidden", 1, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["POST login", 2, 1, "422/Unprocessable Entity", 1, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}]}, function(index, item){
        return item;
    }, [[0, 0]], 0);

});
