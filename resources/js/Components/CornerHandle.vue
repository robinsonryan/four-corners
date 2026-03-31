<script setup lang="ts">
/**
 * CornerHandle - Draggable corner point for document annotation
 *
 * Renders a draggable circular handle at a corner position.
 * Used within the Konva stage to allow users to adjust document corners.
 */
import { computed } from 'vue';
import type { Point, CornerKey } from '../Types';

interface Props {
  cornerKey: CornerKey;
  position: Point;
  color?: string;
  radius?: number;
  strokeWidth?: number;
  draggable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  color: '#007bff',
  radius: 12,
  strokeWidth: 2,
  draggable: true,
});

const emit = defineEmits<{
  dragStart: [cornerKey: CornerKey];
  dragMove: [cornerKey: CornerKey, position: Point];
  dragEnd: [cornerKey: CornerKey, position: Point];
}>();

// Corner label positions
const labelOffset = computed(() => {
  const offsets: Record<CornerKey, { x: number; y: number }> = {
    topLeft: { x: -20, y: -20 },
    topRight: { x: 20, y: -20 },
    bottomRight: { x: 20, y: 20 },
    bottomLeft: { x: -20, y: 20 },
  };
  return offsets[props.cornerKey];
});

const labelText = computed(() => {
  const labels: Record<CornerKey, string> = {
    topLeft: 'TL',
    topRight: 'TR',
    bottomRight: 'BR',
    bottomLeft: 'BL',
  };
  return labels[props.cornerKey];
});

const handleDragStart = () => {
  emit('dragStart', props.cornerKey);
};

const handleDragMove = (e: any) => {
  const node = e.target;
  emit('dragMove', props.cornerKey, {
    x: node.x(),
    y: node.y(),
  });
};

const handleDragEnd = (e: any) => {
  const node = e.target;
  emit('dragEnd', props.cornerKey, {
    x: node.x(),
    y: node.y(),
  });
};

// Konva circle config
const circleConfig = computed(() => ({
  x: props.position.x,
  y: props.position.y,
  radius: props.radius,
  fill: 'white',
  stroke: props.color,
  strokeWidth: props.strokeWidth,
  draggable: props.draggable,
  shadowColor: 'black',
  shadowBlur: 4,
  shadowOpacity: 0.3,
  shadowOffset: { x: 1, y: 1 },
}));

// Label config
const labelConfig = computed(() => ({
  x: props.position.x + labelOffset.value.x,
  y: props.position.y + labelOffset.value.y,
  text: labelText.value,
  fontSize: 12,
  fontFamily: 'Arial',
  fill: props.color,
  fontStyle: 'bold',
  align: 'center',
  verticalAlign: 'middle',
  offsetX: 8,
  offsetY: 6,
}));
</script>

<template>
  <v-group>
    <!-- Corner label -->
    <v-text :config="labelConfig" />

    <!-- Draggable handle -->
    <v-circle
      :config="circleConfig"
      @dragstart="handleDragStart"
      @dragmove="handleDragMove"
      @dragend="handleDragEnd"
    />
  </v-group>
</template>
