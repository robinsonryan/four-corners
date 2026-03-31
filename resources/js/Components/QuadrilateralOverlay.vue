<script setup lang="ts">
/**
 * QuadrilateralOverlay - Renders the selection quadrilateral
 *
 * Draws a semi-transparent polygon connecting the four corners,
 * with stroke and fill to indicate the selected document area.
 */
import { computed } from 'vue';
import type { Corners } from '../Types';

interface Props {
  corners: Corners;
  fillColor?: string;
  fillOpacity?: number;
  strokeColor?: string;
  strokeWidth?: number;
}

const props = withDefaults(defineProps<Props>(), {
  fillColor: '#007bff',
  fillOpacity: 0.2,
  strokeColor: '#007bff',
  strokeWidth: 2,
});

// Convert corners to flat point array for Konva Line
const points = computed(() => [
  props.corners.topLeft.x,
  props.corners.topLeft.y,
  props.corners.topRight.x,
  props.corners.topRight.y,
  props.corners.bottomRight.x,
  props.corners.bottomRight.y,
  props.corners.bottomLeft.x,
  props.corners.bottomLeft.y,
]);

// Konva line config (closed polygon)
const lineConfig = computed(() => ({
  points: points.value,
  fill: props.fillColor,
  opacity: props.fillOpacity,
  stroke: props.strokeColor,
  strokeWidth: props.strokeWidth,
  closed: true,
  lineCap: 'round',
  lineJoin: 'round',
}));

// Edge lines for better visibility
const edgeConfigs = computed(() => {
  const edges = [
    [props.corners.topLeft, props.corners.topRight],
    [props.corners.topRight, props.corners.bottomRight],
    [props.corners.bottomRight, props.corners.bottomLeft],
    [props.corners.bottomLeft, props.corners.topLeft],
  ];

  return edges.map((edge, index) => ({
    key: `edge-${index}`,
    points: [edge[0].x, edge[0].y, edge[1].x, edge[1].y],
    stroke: props.strokeColor,
    strokeWidth: props.strokeWidth,
    lineCap: 'round',
  }));
});
</script>

<template>
  <v-group>
    <!-- Filled polygon -->
    <v-line :config="lineConfig" />

    <!-- Edge lines -->
    <v-line
      v-for="edge in edgeConfigs"
      :key="edge.key"
      :config="edge"
    />
  </v-group>
</template>
