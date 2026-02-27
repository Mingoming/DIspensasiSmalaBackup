<script setup>
import { ref, onMounted, watch } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
})

const chartData = ref({
  labels: [],
  datasets: []
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1
      }
    }
  }
}

function updateChartData() {
  if (!props.data || props.data.length === 0) return

  chartData.value = {
    labels: props.data.map(item => item.kelas),
    datasets: [
      {
        label: 'Total',
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        data: props.data.map(item => item.total)
      },
      {
        label: 'Disetujui',
        backgroundColor: 'rgba(34, 197, 94, 0.8)',
        data: props.data.map(item => item.approved)
      },
      {
        label: 'Ditolak',
        backgroundColor: 'rgba(239, 68, 68, 0.8)',
        data: props.data.map(item => item.rejected)
      }
    ]
  }
}

watch(() => props.data, updateChartData, { deep: true })

onMounted(() => {
  updateChartData()
})
</script>

<template>
  <div class="h-64 sm:h-80">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>