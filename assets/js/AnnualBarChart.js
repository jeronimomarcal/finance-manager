// assets/js/AnnualBarChart.js

class AnnualBarChart {
    constructor(canvasId, categories, expenses, incomes) {
        this.canvasId = canvasId;
        this.categories = categories;
        this.expenses = expenses;
        this.incomes = incomes;
    }

    init() {
        const ctx = document.getElementById(this.canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.categories,
                datasets: [{
                    label: 'Despesas',
                    data: this.expenses,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Ganhos',
                    data: this.incomes,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }
}
