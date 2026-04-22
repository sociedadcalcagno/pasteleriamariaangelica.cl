import fs from 'node:fs'
import path from 'node:path'
import { defineConfig } from 'vite'

function copyServerFiles() {
  return {
    name: 'copy-server-files',
    closeBundle() {
      const rootDir = process.cwd()
      const outDir = path.join(rootDir, 'dist')

      const imageDir = path.join(rootDir, 'img')
      const imageDestination = path.join(outDir, 'img')
      if (fs.existsSync(imageDir)) {
        fs.cpSync(imageDir, imageDestination, { recursive: true })
      }
    }
  }
}

export default defineConfig({
  root: '.',
  plugins: [copyServerFiles()],
  build: {
    outDir: 'dist'
  }
})
