(function($) {
    'use strict';

    $(document).ready(function() {
        function createChart(elementId, label, data, type = 'bar') {
            var ctx = document.querySelector('#' + elementId + ' canvas').getContext('2d');
            new Chart(ctx, {
                type: type,
                data: {
                    labels: data.map(item => item.label),
                    datasets: [{
                        label: label,
                        data: data.map(item => item.value),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        createChart('dc-event-views-chart', 'Event Views', dcEventAnalyticsData.views);
        createChart('dc-event-attendance-chart', 'Event Attendance', dcEventAnalyticsData.attendance);
        createChart('dc-event-conversion-chart', 'Conversion Rate (%)', dcEventAnalyticsData.conversion, 'line');
        createChart('dc-event-engagement-chart', 'Engagement Rate (%)', dcEventAnalyticsData.engagement, 'line');
        createChart('dc-event-popularity-trend-chart', 'Event Popularity Trend', dcEventAnalyticsData.popularity_trend, 'line');

        // Create a stacked bar chart for user engagement patterns
        var ctx = document.querySelector('#dc-event-user-engagement-chart canvas').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dcEventAnalyticsData.user_engagement_patterns.map(item => item.label),
                datasets: createEngagementDatasets(dcEventAnalyticsData.user_engagement_patterns)
            },
            options: {
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });

        function createEngagementDatasets(data) {
            var engagementTypes = [];
            data.forEach(event => {
                event.engagements.forEach(engagement => {
                    if (!engagementTypes.includes(engagement.type)) {
                        engagementTypes.push(engagement.type);
                    }
                });
            });

            return engagementTypes.map(type => ({
                label: type,
                data: data.map(event => {
                    var engagement = event.engagements.find(e => e.type === type);
                    return engagement ? engagement.count : 0;
                }),
                backgroundColor: getRandomColor()
            }));
        }

        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        $('#dc-export-analytics').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dc_export_analytics',
                    nonce: dc_event_manager.nonce
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob) {
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'event_analytics_export.csv';
                    link.click();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Export failed:', textStatus, errorThrown);
                    alert('Export failed. Please try again.');
                }
            });
        });
    });
})(jQuery);