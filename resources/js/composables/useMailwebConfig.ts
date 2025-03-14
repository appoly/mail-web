// Define the MailwebConfig interface
export interface MailwebConfig {
  deleteAllEnabled: boolean;
  sendSampleButton: boolean;
  return: {
    appName: string;
    appUrl: string;
  };
}

// Declare the global window interface extension
declare global {
  interface Window {
    mailwebConfig?: MailwebConfig;
  }
}

/**
 * Composable to access the mailweb configuration
 * This provides a centralized way to access the config that is injected
 * by the MailWebServiceProvider
 */
export function useMailwebConfig() {
  // Default configuration values
  const defaultConfig: MailwebConfig = {
    deleteAllEnabled: false,
    sendSampleButton: true,
    return: {
      appName: 'App',
      appUrl: '/',
    },
  };

  // Get the config from the window object or use defaults
  const getConfig = (): MailwebConfig => {
    return window.mailwebConfig || defaultConfig;
  };

  // Individual getters for specific config values
  const isDeleteAllEnabled = (): boolean => {
    return getConfig().deleteAllEnabled;
  };

  const isSendSampleButtonEnabled = (): boolean => {
    return getConfig().sendSampleButton;
  };

  const getReturnConfig = (): { appName: string; appUrl: string } => {
    return getConfig().return;
  };

  return {
    getConfig,
    isDeleteAllEnabled,
    isSendSampleButtonEnabled,
    getReturnConfig,
  };
}
