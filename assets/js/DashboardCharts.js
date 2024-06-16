class DashboardCharts {
    constructor(categories, expenses, incomes, totalExpense, totalIncome) {
        this.categories = categories;
        this.expenses = expenses;
        this.incomes = incomes;
        this.totalExpense = totalExpense;
        this.totalIncome = totalIncome;

        this.initCharts();
    }

    initCharts() {
        this.initLineChart();
        this.initBarChart();
        this.initPieChart();
    }

    initPieChart() {
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Despesas', 'Ganhos'],
                datasets: [{
                    label: 'Despesas vs Ganhos',
                    data: [this.totalExpense, this.totalIncome],
                    backgroundColor: ['#dc3545', '#28a745']
                }]
            }
        });
    }

    initBarChart() {
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
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

    initLineChart() {
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: this.categories,
                datasets: [{
                    label: 'Despesas',
                    data: this.expenses,
                    fill: false,
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Ganhos',
                    data: this.incomes,
                    fill: false,
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

// Inicialize os gráficos passando os dados necessários
const categories = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio'];
const expenses = [100, 200, 300, 400, 500];
const incomes = [150, 250, 350, 450, 550];
const totalExpense = 1500;
const totalIncome = 1750;

new DashboardCharts(categories, expenses, incomes, totalExpense, totalIncome);
