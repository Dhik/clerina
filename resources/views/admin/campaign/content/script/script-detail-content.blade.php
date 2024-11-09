<script>
    $(document).on('click', '.btnDetail', detailContent);

    let statisticDetailChart;

    function detailContent() {
        let $btnRow = $(this).closest('tr');

        if ($btnRow.hasClass('child')) {
            $btnRow = $btnRow.prev();
        }
        let rowData = contentTable.row($btnRow).data();

        if (!rowData) {
            console.log("Row data is still undefined.");
            return;
        }

        console.log(rowData);

        // Update modal with the data
        $('#likeModal').text(rowData.like);
        $('#viewModal').text(rowData.view);
        $('#commentModal').text(rowData.comment);
        $('#rateCardModal').text(rowData.rate_card_formatted);
        $('#kodeAdsModal').text(rowData.kode_ads);

        if (rowData.upload_date !== null) {
            $('#uploadDateModal').text(rowData.upload_date);
        } else {
            $('#uploadDateModal').text('Belum Posting');
        }

        // Clear existing chart if it exists
        if (statisticDetailChart) {
            statisticDetailChart.destroy();
        }

        // Fetch and render the chart data
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

        // Embed content based on the channel
        $('#contentEmbed').html(''); // Clear previous content

        if (rowData.link !== '' && rowData.channel === 'twitter_post') {
            let twitterLink = rowData.link.replace('https://x.com/', 'https://twitter.com/');

            // Dynamically insert the Twitter post URL
            let tweetEmbed = `
                <blockquote class="twitter-tweet">
                    <a href="${twitterLink}"></a>
                </blockquote>
            `;
            $('#contentEmbed').html(tweetEmbed);

            // Render the Twitter embed
            twttr.widgets.load(document.getElementById('contentEmbed'));

        } else if (rowData.link !== '' && rowData.channel === 'tiktok_video') {
            // Embed TikTok video
            $.ajax({
                url: "https://www.tiktok.com/oembed?url=" + rowData.link,
                type: 'GET',
                success: function (response) {
                    $('#contentEmbed').html(response.html);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else if (rowData.link !== '' && rowData.channel === 'instagram_feed') {
            // Embed Instagram post
            let cleanLink = rowData.link.split('?')[0];
            let linkIg = cleanLink.endsWith('/');
            let embedLink = linkIg ? cleanLink + 'embed' : cleanLink + '/embed';

            let embedIg = '<iframe width="315" height="560" src="' + embedLink + '" frameborder="0"></iframe>';
            $('#contentEmbed').html(embedIg);
        } else if (rowData.link !== '' && rowData.channel === 'youtube_video') {
            // Embed YouTube Shorts
            let videoId = rowData.link.split('/').pop(); // Extract video ID from URL
            let embedLink = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1'; // Embed URL with autoplay

            let embedYoutube = `<iframe width="315" height="560" src="${embedLink}" frameborder="0" allowfullscreen></iframe>`;
            $('#contentEmbed').html(embedYoutube);

        } else if (rowData.link !== '' && rowData.channel === 'shopee_video') {
            let shopeeEmbed = `<iframe src="${rowData.link}" width="315" height="560" frameborder="0" allowfullscreen></iframe>`;
            $('#contentEmbed').html(shopeeEmbed);

        } else if (rowData.link !== '') {
            // Embed generic link
            let buttonEmbed = '<a href="'+ rowData.link +'" target="_blank" class="btn btn-primary">Go to Content</a>';
            $('#contentEmbed').html(buttonEmbed);
        } else {
            $('#contentEmbed').html('');  // Clear contentEmbed if no link is provided
        }

        // Show the modal
        $('#detailModal').modal('show');
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
                plugins: {
                    title: {
                        display: true,
                        text: 'Statistics Chart'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    }
                }
            }
        });
    }
</script>
