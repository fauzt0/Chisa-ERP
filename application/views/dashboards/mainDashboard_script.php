<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Bar chart
    new Chart(document.getElementById("chartjs-dashboard-bar"), {
      type: "bar",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Last year",
          backgroundColor: window.cssVariables.primary,
          borderColor: window.cssVariables.primary,
          hoverBackgroundColor: window.cssVariables.primary,
          hoverBorderColor: window.cssVariables.primary,
          data: [54, 67, 41, 55, 62, 45, 55, 73, 60, 76, 48, 79],
          barPercentage: .325,
          categoryPercentage: .5
        }, {
          label: "This year",
          backgroundColor: window.cssVariables.primarySubtle,
          borderColor: window.cssVariables.primarySubtle,
          hoverBackgroundColor: window.cssVariables.primarySubtle,
          hoverBorderColor: window.cssVariables.primarySubtle,
          data: [69, 66, 24, 48, 52, 51, 44, 53, 62, 79, 51, 68],
          barPercentage: .325,
          categoryPercentage: .5
        }]
      },
      options: {
        maintainAspectRatio: false,
        cornerRadius: 15,
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            gridLines: {
              display: false
            },
            ticks: {
              stepSize: 20
            },
            stacked: true,
          }],
          xAxes: [{
            gridLines: {
              color: "transparent"
            },
            stacked: true,
          }]
        }
      }
    });
  });
</script>
<script>
  // Workaround for theme switch re-initialization issue
  var isTempusDominusInitialized = false;
  document.addEventListener("DOMContentLoaded", function() {
    if (isTempusDominusInitialized) {
      return;
    }
    isTempusDominusInitialized = true;
    new tempusDominus.TempusDominus(document.getElementById('calendar-dashboard'), {
      display: {
        inline: true,
        components: {
          clock: false,
          hours: false,
          minutes: false
        }
      }
    });
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Pie chart
    new Chart(document.getElementById("chartjs-dashboard-pie"), {
      type: "pie",
      data: {
        labels: ["Direct", "Affiliate", "E-mail", "Other"],
        datasets: [{
          data: [2602, 1253, 541, 1465],
          backgroundColor: [
            window.cssVariables.primary,
            window.cssVariables.warning,
            window.cssVariables.danger,
            "#E8EAED"
          ],
        }]
      },
      options: {
        responsive: !window.MSInputMethodContext,
        maintainAspectRatio: false,
        cutoutPercentage: 70,
        legend: {
          display: false
        },
        elements: {
          arc: {
            borderWidth: 5,
            borderColor: window.cssVariables.secondaryBg
          }
        },
      }
    });
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    $("#datatables-dashboard-projects").DataTable({
      destroy: true,
      pageLength: 6,
      lengthChange: false,
      bFilter: false,
      autoWidth: false
    });
  });
</script>