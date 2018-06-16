function getServerStat(_chartConfig)
{
  $.ajax({
    url: '{{url.here}}?server=status',
    success: function (_response)
    {
      _response = JSON.parse(_response);
      if(_response)
      {
        addNewServerData(_chartConfig, _response)
      }

      setTimeout(function ()
      {
        getServerStat(_chartConfig);
      }, 1000);
    }
  });
}


function addNewServerData(_chartConfig, _result)
{
  if (_chartConfig.data.datasets.length > 0)
  {
    // logy(_result);
    if(_result)
    {
      _chartConfig.data.labels.push(_result.time);

      _chartConfig.data.datasets[0].data.push(_result.cpu);
      _chartConfig.data.datasets[1].data.push(_result.memory);
      _chartConfig.data.datasets[2].data.push(_result.disk);
      removeOldServerData();

      window.myLine.update();
    }
  }
}


function removeOldServerData()
{
    if(window.myLine.config.data.labels.length > 60)
    {
      window.myLine.config.data.labels.shift();
    }

    window.myLine.config.data.datasets.forEach(function(dataset)
    {
      if(dataset.data.length > 60)
      {
        dataset.data.shift();
      }
    });

    window.myLine.update();
}



function chartDrawer()
{
    // var timeFormat = 'MM/DD/YYYY HH:mm';
    var color      = Chart.helpers.color;
    var config     =
    {
      type: 'line',
      data:
      {
        labels:
        [
        ],
        datasets: [
        {
          label: '{%trans "CPU Usage"%}',
          backgroundColor: color(window.chartColors.red).alpha(0.7).rgbString(),
          borderColor: window.chartColors.red,
          fill: false,
          data:
          [
          ],
        },
        {
          label: '{%trans "Memory"%}',
          backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
          borderColor: window.chartColors.blue,
          fill: false,
          data:
          [
          ],
        },
        {
          label: '{%trans "Disk usage"%}',
          backgroundColor: color(window.chartColors.green).alpha(0.5).rgbString(),
          borderColor: window.chartColors.green,
          fill: false,
          data:
          [
          ],
        }]
      },
      options:
      {
        title:
        {
          text: 'Server usage'
        },
        scales:
        {
          // xAxes: [{
          //   type: 'time',
          //   display: true,
          // }],
          yAxes: [
          {
            ticks:
            {
              min: 0,
              // max: 100,
              // the data minimum used for determining the ticks is Math.min(dataMin, suggestedMin)
              // suggestedMin: 0,
              // the data maximum used for determining the ticks is Math.max(dataMax, suggestedMax)
              suggestedMax: 100
            },
            scaleLabel:
            {
              display: true,
              labelString: '{%trans "percentage"%}'
            }
          }]
        },
        tooltips:
        {
          position: 'nearest',
          mode: 'index',
          intersect: false,
        }
      }
    };

    window.onload = function()
    {
      var ctx = document.getElementById('canvas').getContext('2d');
      window.myLine = new Chart(ctx, config);

    };

    getServerStat(config);
}

