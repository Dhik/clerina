<script>
    $(document).on('click', '.btnDetail', detailContent());

    let statisticDetailChart;

    function detailContent() {
        return function () {
            let rowData = contentTable.row($(this).closest('tr')).data();

            $('#likeModal').text(rowData.like);
            $('#viewModal').text(rowData.view);
            $('#commentModal').text(rowData.comment);
            $('#rateCardModal').text(rowData.rate_card_formatted);

            if (rowData.upload_date !== null) {
                $('#uploadDateModal').text(rowData.upload_date);
            }

            // Clear existing chart if it exists
            if (statisticDetailChart) {
                statisticDetailChart.destroy();
            }

            $.ajax({
                url: "{{ route('statistic.chartDetail', ['campaignContentId' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id),
                type: 'GET',
                success: function (response) {
                    renderDetailChart(response);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });

            if (rowData.link !== '' && rowData.channel === 'tiktok_video') {
                $.ajax({
                    url: "https://www.tiktok.com/oembed?url=" + rowData.link,
                    type: 'GET',
                    success: function (response) {
                        $('#contentEmbed').html(response.html)
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            } else if (rowData.link !== '' && rowData.channel === 'instagram_feed') {
                let linkIg = rowData.link.endsWith('/');
                let embedLink = linkIg ? rowData.link + 'embed' : rowData.link + '/embed';

                let embedIg = '<iframe height="600" src="' + embedLink + '" frameborder="0"></iframe>'
                $('#contentEmbed').html(embedIg)
            } else if (rowData.link !== '') {
                let buttonEmbed = '<a href='+ rowData.link +' target="_blank" class="btn btn-primary">Go to Content</a>';
                $('#contentEmbed').html(buttonEmbed)
            } else {
                $('#contentEmbed').text('')
            }

            $('#detailModal').modal('show');
        }
    }

    function renderDetailChart(chartData) {
        // Set up the Chart.js configuration
        let ctx = document.getElementById('statisticDetailChart').getContext('2d');
        statisticDetailChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(data => data.date),
                datasets: [{
                    label: 'Views',
                    data: chartData.map(data => data.view),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false
                },
                    {
                        label: 'Likes',
                        data: chartData.map(data => data.positive_like),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        fill: false
                    },
                    {
                        label: 'Comments',
                        data: chartData.map(data => data.comment),
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        fill: false
                    }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Statistics Chart'
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Date'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        });
    }
</script>
