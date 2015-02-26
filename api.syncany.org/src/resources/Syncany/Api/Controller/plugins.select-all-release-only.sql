select * from plugins
where
      pluginRelease=1
	and (pluginOperatingSystem='all' or pluginOperatingSystem=:pluginOperatingSystem)
	and (pluginArchitecture='all' or pluginArchitecture=:pluginArchitecture)
order by id desc