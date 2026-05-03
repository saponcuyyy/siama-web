<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { watch } from 'vue'

const props = defineProps({
    modelValue: String,
    placeholder: String
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
    ],
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML())
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl focus:outline-none min-h-[200px] max-h-[500px] overflow-y-auto p-4 border border-slate-200 rounded-xl bg-white'
        }
    }
})

watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value
    if (!isSame) {
        editor.value.commands.setContent(value, false)
    }
})
</script>

<template>
    <div v-if="editor" class="space-y-2">
        <!-- Toolbar -->
        <div class="flex flex-wrap gap-1 p-2 bg-slate-50 border border-slate-200 rounded-t-xl">
            <button 
                type="button"
                @click="editor.chain().focus().toggleBold().run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('bold')}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
                title="Bold"
            >
                <b>B</b>
            </button>
            <button 
                type="button"
                @click="editor.chain().focus().toggleItalic().run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('italic')}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
                title="Italic"
            >
                <i>I</i>
            </button>
            <button 
                type="button"
                @click="editor.chain().focus().toggleStrike().run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('strike')}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
                title="Strike"
            >
                <s>S</s>
            </button>
            <div class="w-px h-6 bg-slate-300 mx-1"></div>
            <button 
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('heading', { level: 1 })}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors font-bold"
            >
                H1
            </button>
            <button 
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('heading', { level: 2 })}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors font-bold"
            >
                H2
            </button>
            <div class="w-px h-6 bg-slate-300 mx-1"></div>
            <button 
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('bulletList')}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
            >
                • List
            </button>
            <button 
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()" 
                :class="{'bg-blue-100 text-blue-600': editor.isActive('orderedList')}"
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
            >
                1. List
            </button>
            <div class="w-px h-6 bg-slate-300 mx-1"></div>
            <button 
                type="button"
                @click="editor.chain().focus().undo().run()" 
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
            >
                ⟲
            </button>
            <button 
                type="button"
                @click="editor.chain().focus().redo().run()" 
                class="p-1.5 rounded hover:bg-slate-200 transition-colors"
            >
                ⟳
            </button>
        </div>
        <editor-content :editor="editor" />
    </div>
</template>

<style>
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #adb5bd;
  pointer-events: none;
  height: 0;
}
</style>
