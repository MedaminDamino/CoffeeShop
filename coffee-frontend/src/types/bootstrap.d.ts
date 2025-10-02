declare module 'bootstrap' {
  export class Modal {
    constructor(element: HTMLElement, options?: Partial<Modal.Options>)
    show(): void
    hide(): void
    toggle(): void
    dispose(): void
    static getInstance(element: HTMLElement): Modal | null
    static getOrCreateInstance(element: HTMLElement): Modal
  }

  export namespace Modal {
    interface Options {
      backdrop: boolean | 'static'
      keyboard: boolean
      focus: boolean
    }
  }
}